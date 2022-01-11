<?php


namespace App\Console\Commands\ProducerReceiver;


use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class AMQPReceiver extends Command
{
    protected $signature = 'amqp:receiver';
    protected $description = 'Represents amqp consumer';

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

    public function handle(): void
    {
        $this->channel->queue_declare(
            'hello',
            false,
            true, // will survive rabbit server restart
            false,
            false
        );

        $this->comment('Waiting for messages...');

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            'hello',
            '',
            false,
            false,
            false,
            false,
            fn($msg) => $this->consume($msg)
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function consume(AMQPMessage $message): void
    {
        $this->alert($message->getBody());
        $message->ack();
    }
}
