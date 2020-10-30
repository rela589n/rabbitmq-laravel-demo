<?php

namespace App\Console;

use App\Console\Commands\FinalPubSub\OverallConsumer;
use App\Console\Commands\FinalPubSub\Publisher;
use App\Console\Commands\FinalPubSub\Subscriber;
use App\Console\Commands\ProducerReceiver\AMQPProducer;
use App\Console\Commands\ProducerReceiver\DeleteQueue;
use App\Console\Commands\PublisherSubscriber\AMQPLogsProducer;
use App\Console\Commands\PublisherSubscriber\AMQPLogsReceiver;
use App\Console\Commands\RabbitWorker\AMQPPusher;
use App\Console\Commands\RabbitWorker\AMQPWorker;
use App\Console\Commands\Routing\AMQPAllLogsReceiver;
use App\Console\Commands\Routing\AMQPDirectLogsProducer;
use App\Console\Commands\Routing\AMQPOnlyErrorLogsReceiver;
use App\Console\Commands\RPC\RPCClient;
use App\Console\Commands\RPC\RPCWorker;
use App\Console\Commands\Topic\StructuredLogsProducer;
use App\Console\Commands\Topic\StructuredLogsReceiver;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        AMQPProducer::class,
        AMQPPusher::class,
        AMQPWorker::class,
        DeleteQueue::class,
        AMQPLogsProducer::class,
        AMQPLogsReceiver::class,
        AMQPDirectLogsProducer::class,
        AMQPOnlyErrorLogsReceiver::class,
        AMQPAllLogsReceiver::class,
        StructuredLogsProducer::class,
        StructuredLogsReceiver::class,
        RPCWorker::class,
        RPCClient::class,
        Publisher::class,
        Subscriber::class,
        OverallConsumer::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
