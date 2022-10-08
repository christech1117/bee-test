<?php

namespace App\AMQP;

use AMQPConnectionException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessagePublisher
{
    public function __construct()
    {

    }

    /**
     * connect to rabbitmq
     *
     * @param string $connection
     * @return void
     * @throws AMQPConnectionException
     */
    public function connect(string $connection)
    {
        if (empty($connection)) {
            throw new AMQPConnectionException('driver is empty!');
        }

        $host     = config("rabbitmq.$connection.host");
        $port     = config("rabbitmq.$connection.port");
        $user     = config("rabbitmq.$connection.user");
        $password = config("rabbitmq.$connection.password");
        $this->queue = config("rabbitmq.$connection.queue");
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->channel    = $this->connection->channel();
        $this->channel->queue_declare(
            $this->queue,
            false,
            true,
            false,
            false,
        );
    }

    public function publish(string $message)
    {
        $amqpMessage = new AMQPMessage($message);
        $this->channel->basic_publish($amqpMessage, '', $this->queue);
    }
}
