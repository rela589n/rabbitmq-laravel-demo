<?php

namespace App\Console\Commands\RabbitWorker;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPWorker extends Command
{
    protected $signature = 'amqp:work';
    protected $description = 'Processes queue';

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

        $this->comment('Waiting for jobs...');

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            'jobs',
            '',
            false,
            false,
            false,
            false,
            fn($msg) => $this->process($msg)
        );

        while ($this->channel->is_consuming()) {
            $this->comment('consuming..');
            $this->channel->wait();
        }
    }

    private function process(AMQPMessage $message): void
    {
        $body = json_decode($message->getBody(), true);

        $this->comment('Received: '.$body['message']);
        $this->comment('Estimated time:'.$body['time']);

        sleep($body['time']);
        $message->ack();

        $this->info('Done!');
    }
}
