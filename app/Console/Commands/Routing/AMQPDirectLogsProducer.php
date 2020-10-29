<?php

namespace App\Console\Commands\Routing;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPDirectLogsProducer extends Command
{
    protected $signature = 'amqp:produce-direct-logs';
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

    private array $routes = [
        'info',
        'warnings',
        'errors',
    ];

    public function handle(): void
    {
        $this->channel->exchange_declare(
            'direct_logs',
            'direct',
            false,
            true,
            false
        );

        $logsCount = (int)$this->ask('Number of logs?');

        for ($i = 0; $i < $logsCount; ++$i) {
            $route = $this->routes[array_rand($this->routes)];

            $body = [
                'message' => 'Log with '.$route.' severity',
                'severity' => $route,
            ];

            $message = new AMQPMessage(
                json_encode($body, JSON_THROW_ON_ERROR),
                [
                    'content-type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ],
            );

            $this->channel->basic_publish(
                $message,
                'direct_logs',
                $route
            );
        }
    }
}
