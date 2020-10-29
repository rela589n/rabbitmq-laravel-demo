<?php

namespace App\Console\Commands\PublisherSubscriber;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPLogsReceiver extends Command
{
    protected $signature = 'amqp:receive-logs';
    protected $description = 'Command description';

    private AMQPChannel $channel;

    public function __construct(AMQPChannel $channel)
    {
        parent::__construct();
        $this->channel = $channel;
    }

    public function __destruct()
    {
        $this->channel->close();
    }

    public function handle()
    {
        $this->channel->exchange_declare(
            'logs',
            'fanout',
            false,
            true,
            false
        );

        [$queueName] = $this->channel->queue_declare(
            '',
            false,
            true,
            true,
            false
        );

        $this->channel->queue_bind($queueName, 'logs');

        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            fn($msg) => $this->consume($msg),
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function consume(AMQPMessage $message): void
    {
        $this->alert($message->getBody());

        if ($this->confirm('confirm ack?')) {
            $message->ack();
        }
    }
}
