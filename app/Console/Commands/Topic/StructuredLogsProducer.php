<?php

namespace App\Console\Commands\Topic;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class StructuredLogsProducer extends Command
{
    protected $signature = 'amqp:topic-produce';
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

        do {
            $routingKey = $this->ask('Enter route string', 'system.warn');
            $message = new AMQPMessage(
                'Log to route '.$routingKey,
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $this->channel->basic_publish(
                $message,
                'topic_logs',
                $routingKey
            );
        } while (true);
    }
}
