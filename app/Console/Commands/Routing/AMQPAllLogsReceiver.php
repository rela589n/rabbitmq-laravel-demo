<?php

namespace App\Console\Commands\Routing;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPAllLogsReceiver extends Command
{
    protected $signature = 'amqp:receive-all-logs';
    protected $description = 'Command description';

    private AMQPChannel $channel;
    private int $currentMessageIndex = -1;

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

    public function handle()
    {
        $this->channel->exchange_declare(
            'direct_logs',
            'direct',
            false,
            true,
            false
        );

        [$queueName] = $this->channel->queue_declare(
            'logs',
            false,
            true,
            false,
            false,
        );

        foreach ($this->routes as $route) {
            $this->channel->queue_bind(
                $queueName,
                'direct_logs',
                $route,
            );
        }

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            true,
            false,
            fn($msg) => $this->consume($msg)
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function consume(AMQPMessage $message): void
    {
        $body = $message->getBody();
        $body = json_decode($body, true);

        $this->alert($this->nextMessageIndex().$body['message']);

        $message->ack();
    }

    private function nextMessageIndex(): int
    {
        return ++$this->currentMessageIndex;
    }
}
