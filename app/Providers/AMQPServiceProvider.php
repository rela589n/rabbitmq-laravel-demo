<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;


final class AMQPServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        //
    }
}
