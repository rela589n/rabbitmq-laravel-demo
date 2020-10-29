<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;


final class AMQPServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            AbstractConnection::class,
            function () {
                $config = config('queue.connections.rabbitmq.hosts.messaging');

                return $this->app->make(
                    AMQPStreamConnection::class,
                    [
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'user' => $config['user'],
                        'password' => $config['password'],
                        'vhost' => $config['vhost'],
                    ]
                );
            }
        );

        $this->app->bind(
            AMQPChannel::class,
            function () {
                return $this->app->make(AbstractConnection::class)
                    ->channel();
            }
        );
    }

    public function boot()
    {
        //
    }
}
