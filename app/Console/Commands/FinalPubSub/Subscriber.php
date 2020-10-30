<?php

namespace App\Console\Commands\FinalPubSub;

use App\Console\Services\AMQP\MessagesConsumer;
use Illuminate\Console\Command;

class Subscriber extends Command
{
    protected $signature = 'amqp:final-consume';
    protected $description = 'Command description';
    private MessagesConsumer $consumer;

    public function __construct(MessagesConsumer $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    public function handle(): void
    {
        $route = $this->ask('What route subscribe to?', '#');

        $this->consumer->consume(
            $route,
            function (string $body) {
                $this->info($body);

                return true;
            }
        );
    }
}
