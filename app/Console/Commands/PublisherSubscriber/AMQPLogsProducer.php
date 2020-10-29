<?php

namespace App\Console\Commands\PublisherSubscriber;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPLogsProducer extends Command
{
    protected $signature = 'amqp:produce-logs';
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

    private array $logs = [
        'first',
        'second',
        'third',
    ];

    public function handle(): void
    {
        $this->channel->exchange_declare(
            'logs',
            'fanout',
            false,
            true,
            false
        );

        $logsCount = (int)$this->ask('Number of logs?');

        for ($i = 0; $i < $logsCount; ++$i) {
            $message = new AMQPMessage(
                $this->logs[array_rand($this->logs)],
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
            );
            $this->channel->basic_publish($message, 'logs');
        }
    }
}
