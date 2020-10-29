<?php

namespace App\Console\Commands\Topic;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class StructuredLogsReceiver extends Command
{
    protected $signature = 'amqp:topic-receive';
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
            'topic_logs',
            'topic',
            false,
            true,
            false
        );

        $routingKey = $this->ask('Which logs would you like to consume? Enter routing template: ', '#');
        [$queueName] = $this->channel->queue_declare(
            '',
            false,
            true,
            true,
            false
        );

        $this->channel->queue_bind(
            $queueName,
            'topic_logs',
            $routingKey,
        );

        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            true,
            false,
            fn($msg) => $this->consume($msg),
        );

        while($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function consume(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $this->alert($body);

        $message->ack();
    }
}
