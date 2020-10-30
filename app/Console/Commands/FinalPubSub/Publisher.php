<?php

namespace App\Console\Commands\FinalPubSub;

use Bschmitt\Amqp\Amqp;
use Illuminate\Console\Command;

class Publisher extends Command
{
    protected $signature = 'amqp:final-publish';
    protected $description = 'Command description';

    private Amqp $amqp;

    public function __construct(Amqp $amqp)
    {
        parent::__construct();
        $this->amqp = $amqp;
    }

    public function handle()
    {
        while (true) {
            $type = $this->ask('Which type to use?', 'sys');
            $subType = $this->ask('Which subtype to use?', 'info');
            $count = (int)$this->ask('How much messages?', '5');

            $this->publishMessages("$type.$subType", $count);
        }
    }

    private function publishMessages(string $route, int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $this->amqp->publish($route, "Message from [$route]");
        }
    }
}
