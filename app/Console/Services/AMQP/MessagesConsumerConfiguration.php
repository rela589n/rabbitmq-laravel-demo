<?php


namespace App\Console\Services\AMQP;


final class MessagesConsumerConfiguration
{
    private string $queue;
    private bool $persistently;
    private bool $exclusively;
    private bool $forceQueueDeclare;

    public function __construct(
        string $queue,
        bool $persistently,
        bool $exclusively,
        bool $forceQueueDeclare
    ) {
        $this->queue = $queue;
        $this->persistently = $persistently;
        $this->exclusively = $exclusively;
        $this->forceQueueDeclare = $forceQueueDeclare;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function isPersistently(): bool
    {
        return $this->persistently;
    }

    public function isExclusively(): bool
    {
        return $this->exclusively;
    }

    public function needForceQueueDeclare(): bool
    {
        return $this->forceQueueDeclare;
    }
}
