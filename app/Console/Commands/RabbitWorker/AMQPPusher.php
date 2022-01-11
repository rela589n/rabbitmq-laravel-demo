<?php

namespace App\Console\Commands\RabbitWorker;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPPusher extends Command
{
    protected $signature = 'amqp:push';
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
        $this->channel->queue_declare(
            'jobs',
            false,
            true, // will survive rabbit server restart
            false,
            false
        );

        while ($keepPushing ?? true) {
            $time = (int)$this->ask('How much time should it take?');
            $message = new AMQPMessage(
                json_encode(['time' => $time, 'message' => 'New job!'], JSON_THROW_ON_ERROR),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'content-type' => 'application/json',
                ]
            );

            $this->channel->basic_publish($message, '', 'jobs');
            $this->comment('Job sent!');

            $keepPushing = $this->confirm('Continue?');
        }
    }
}
