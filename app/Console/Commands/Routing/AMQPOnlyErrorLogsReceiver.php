<?php

namespace App\Console\Commands\Routing;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPOnlyErrorLogsReceiver extends Command
{
    protected $signature = 'amqp:receive-only-errors';
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

    public function handle(): void
    {
        $this->channel->exchange_declare(
            'direct_logs',
            'direct',
            false,
            true,
            false
        );

        $this->channel->queue_declare(
            'errors',
            false,
            true,
            false,
            false
        );

        $this->channel->queue_bind(
            'errors',
            'direct_logs',
            'errors'
        );

        $this->channel->basic_consume(
            'errors',
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
