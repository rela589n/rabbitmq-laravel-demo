<?php

namespace App\Http\Middleware;

use Closure;
use PhpAmqpLib\Connection\AbstractConnection as AMQPConnection;

class CloseAMQPConnection
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        app(AMQPConnection::class)->close();

        return $response;
    }
}
