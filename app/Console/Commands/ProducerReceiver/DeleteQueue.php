<?php

namespace App\Console\Commands\ProducerReceiver;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;

class DeleteQueue extends Command
{
    protected $signature = 'amqp:delete-hello';

    private AMQPChannel $channel;

    public function __construct(AMQPChannel $channel)
    {
        parent::__construct();
        $this->channel = $channel;
    }

    public function handle()
    {
        $this->channel->queue_delete('hello');

        $this->channel->close();
    }
}
