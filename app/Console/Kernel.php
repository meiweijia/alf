<?php

namespace App\Console;

use App\Console\Commands\AppliedOrder;
use App\Console\Commands\FailedOrder;
use App\Console\Commands\OverdueOrder;
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
        FailedOrder::class,
        AppliedOrder::class,
        OverdueOrder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('failedOrder')->everyMinute()->withoutOverlapping();;//处理已经完成的场地信息
        $schedule->command('appliedOrder')->everyFiveMinutes()->withoutOverlapping();;//处理未支付的订单
        $schedule->command('overdueOrder')->everyTenMinutes()->withoutOverlapping();;//处理已经完成的场地信息
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
