<?php

namespace App\Console\Commands\RPC;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class RPCWorker extends Command
{
    protected $signature = 'amqp:rpc:work';
    protected $description = 'Command description';

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
        [$queueName] = $this->channel->queue_declare(
            'queue_rpc',
            false,
            true,
            false,
            false
        );

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            $queueName,
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

    private function consume(AMQPMessage $request): void
    {
        $replyQueueName = $request->get('reply_to');
        $correlationId = $request->get('correlation_id');
        $requestBody = json_decode($request->getBody(), true);

        $this->comment('reply to:'.$replyQueueName);
        $this->comment('correlation id:'.$correlationId);
        $this->comment('request body:'.var_export($requestBody, true).PHP_EOL);

        $fib = $this->fib(...$requestBody['params']);
        $response = new AMQPMessage(
            json_encode(
                [
                    'data' => $fib
                ],
                JSON_THROW_ON_ERROR
            ),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content-type' => 'application/json',
                'correlation_id' => $correlationId,
            ]
        );

        $this->channel->basic_publish(
            $response,
            '',
            $replyQueueName
        );

        $request->ack();
    }

    private function fib(int $num): int
    {
        $nums = [0, 1, 1];

        if ($num <= 2) {
            return $nums[$num];
        }

        for ($i = 3; $i <= $num; ++$i) {
            $nums[$i] = $nums[$i - 1] + $nums[$i - 2];
        }

        return (int)$nums[$num];
    }
}
