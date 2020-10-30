<?php

namespace App\Console\Commands\FinalPubSub;

use App\Console\Services\AMQP\MessagesConsumer;
use Illuminate\Console\Command;

class OverallConsumer extends Command
{
    protected $signature = 'amqp:final-overall-consume';
    protected $description = 'Command description';

    private MessagesConsumer $consumer;

    public function __construct(MessagesConsumer $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    public function handle(): void
    {
        $this->consumer->consume(
            '#',
            function (string $body) {
                $this->info($body);

                return true;
            }
        );
    }
}
