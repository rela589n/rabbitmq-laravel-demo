<?php

namespace Tests\Feature;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Tests\TestCase;

class SetUpRabbitMQ extends TestCase
{
    public function testBasic()
    {
        dd(app(AbstractConnection::class));
    }
}
