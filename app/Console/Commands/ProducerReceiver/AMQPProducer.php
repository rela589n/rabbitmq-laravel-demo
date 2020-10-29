<?php

namespace App\Console\Commands\ProducerReceiver;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class AMQPProducer extends Command
{
    protected $signature = 'amqp:producer';
    protected $description = 'Represents amqp publisher';

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
        $this->channel->queue_declare(
            'hello',
            false,
            true,
            false,
            false
        );

        $message = $this->ask('Enter message: ');
        $message = new AMQPMessage(
            $message,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $this->channel->basic_publish(
            $message,
            '',
            'hello'
        );

        $this->info('Message sent!');
    }
}
