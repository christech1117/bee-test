<?php

namespace App\Console\Commands;

use AMQPConnectionException;
use App\AMQP\MessageConsumer;
use App\AMQP\MessagePublisher;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class PublishFcmMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish:fcm {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'publish messages to queue notification.fcm';

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
        $message = $this->argument('message') ?? '{"identifier": "fcm-msg-a1beff5ac", "type": "device", "deviceId": "string", "text":"Notification message"}';

        $publisher = new MessagePublisher;
        $publisher->connect('fcm');
        $publisher->publish($message);

        return 0;
    }
}
