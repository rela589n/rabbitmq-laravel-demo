<?php

namespace App\Console\Commands\RPC;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class RPCClient extends Command
{
    protected $signature = 'amqp:rpc:request';
    protected $description = 'Command description';

    private AMQPChannel $channel;
    private bool $responseReceived;

    private string $correlationId;

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
        [$rpcQueueName] = $this->channel->queue_declare(
            'queue_rpc',
            false,
            true,
            false,
            false
        );

        [$responseQueueName] = $this->channel->queue_declare(
            '',
            false,
            true,
            true
        );

        $this->channel->basic_consume(
            $responseQueueName,
            '',
            false,
            true,
            true,
            false,
            fn($msg) => $this->consume($msg),
        );

        while (true) {
            $param = (int)$this->ask('Enter index of fibonacci number: ', 123);

            $this->correlationId = Str::random();

            $message = new AMQPMessage(
                json_encode(
                    [
                        'params' => [$param]
                    ],
                    JSON_THROW_ON_ERROR
                ),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'content-type' => 'application/json',
                    'reply_to' => $responseQueueName,
                    'correlation_id' => $this->correlationId
                ]
            );

            $this->channel->basic_publish(
                $message,
                '',
                $rpcQueueName
            );

            $this->responseReceived = false;

            while (
                !$this->responseReceived
                && $this->channel->is_consuming()
            ) {
                $this->channel->wait();
            }
        }
    }

    private function consume(AMQPMessage $response): void
    {
        if ($this->correlationId !== $response->get('correlation_id')) {
            return;
        }

        $body = json_decode($response->getBody(), true);

        $result = $body['data'];
        $this->info($result);

        $this->responseReceived = true;
    }
}
