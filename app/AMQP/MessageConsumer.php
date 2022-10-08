<?php

namespace App\AMQP;

use AMQPConnectionException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MessageConsumer
{
    protected AMQPStreamConnection $connection;

    protected AMQPChannel $channel;

    protected string $queue;

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

    public function consume(callable $callback)
    {
        if (empty($this->connection)) {
            throw new AMQPConnectionException('not connected!');
        }

        $this->channel->basic_consume(
            $this->queue,
            '',
            false,
            false,
            false,
            false,
            $callback
        );
        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}
