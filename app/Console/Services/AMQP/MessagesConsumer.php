<?php


namespace App\Console\Services\AMQP;


use Bschmitt\Amqp\Amqp;
use Closure;
use PhpAmqpLib\Message\AMQPMessage;

final class MessagesConsumer
{
    private Amqp $amqp;
    private MessagesConsumerConfiguration $configuration;

    public function __construct(Amqp $amqp, MessagesConsumerConfiguration $configuration)
    {
        $this->amqp = $amqp;
        $this->configuration = $configuration;
    }

    public function consume(string $route, Closure $handler): void
    {
        $this->amqp->consume(
            $this->configuration->getQueue(),
            fn(AMQPMessage $msg) => $this->handleConsume($msg, $handler),
            $this->amqpArrayConfig($route)
        );
    }

    private function handleConsume(AMQPMessage $message, Closure $handler): void
    {
        $haveProcessed = $handler($message->getBody());

        if ($haveProcessed) {
            $message->ack();
        } else {
            $message->nack(true);
        }
    }

    private function amqpArrayConfig(string $route): array
    {
        return [
            'routing' => $route,
            'persistent' => $this->configuration->isPersistently(),
            'queue_force_declare' => $this->configuration->needForceQueueDeclare(),
            'queue_exclusive' => $this->configuration->isExclusively(),
        ];
    }
}
