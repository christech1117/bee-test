<?php

namespace App\Console\Commands;

use App\AMQP\MessageConsumer;
use App\AMQP\MessagePublisher;
use App\Models\FailedFcmJob;
use App\Models\FcmJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ConsumeFcmMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:fcm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from queue notification.fcm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $consumer = new MessageConsumer;
        $consumer->connect('fcm');
        $consumer->consume(function (AMQPMessage $msg) {
            try {
                $this->validateMessage($msg->body);

                Log::info('received message: ' . $msg->body);

                $this->publishMessageToAnotherQueue($msg->body);
                $this->publishMessageToFCM($msg->body);
                $this->saveSuccessfulMessage($msg->body);
            } catch (Throwable $e) {
                Log::error($e->getMessage());
                $this->saveFailedMessage($msg->body);
            }
            $msg->ack();
        });

        return 0;
    }

    /**
     * validate message body
     *
     * @param string $body
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function validateMessage(string $body)
    {
        if (!$body = json_decode($body, true)) {
            throw new InvalidArgumentException('message body format error: should be an array');
        }

        $validator = Validator::make($body, [
            'identifier' => 'required|string',
            'type' => 'required|string',
            'deviceId' => 'required|string',
            'text' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException('message body format error:' . $validator->errors()->toJson());
        }

        return true;
    }

    /**
     * @param string $body
     * @return void
     */
    protected function publishMessageToAnotherQueue(string $body)
    {
        Log::info('publish message to queue notification.done:' . $body);

        $connection = 'done';
        $publisher = new MessagePublisher;
        $publisher->connect($connection);
        $publisher->publish($body);
    }

    protected function publishMessageToFCM(string $body)
    {
        Log::info('publish message to fcm:' . $body);

        [
            'text' => $text
        ] = json_decode($body, true);
        $messaging = app('firebase.messaging');
        $message = CloudMessage::withTarget('topic', 'my-topic')
            ->withNotification(Notification::create('Incoming message', $text));

        $messaging->send($message);
    }

    /**
     * @param string $body
     * @return void
     */
    protected function saveSuccessfulMessage(string $body)
    {
        Log::info('save message to fcm_jobs:' . $body);

        [
            'identifier' => $identifier
        ] = json_decode($body, true);

        FcmJob::create([
            'identifier' => $identifier,
            'deliver_at' => now()
        ]);
    }

    /**
     * @param string $body
     * @return void
     */
    protected function saveFailedMessage(string $body)
    {
        Log::info('save message to failed_fcm_jobs:' . $body);

        $identifier = json_decode($body, true)['identifier'] ?? '';

        FailedFcmJob::create([
            'identifier' => $identifier,
            'message_body' => $body
        ]);
    }
}
