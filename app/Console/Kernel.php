<?php

namespace App\Console;

use App\Services\FeedbackProcessorService;
use App\Services\LoginProcessorService;
use App\Services\SalesReceiptService;
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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            LoginProcessorService::remapTemporaryCounterUserNumbersToIds();
            FeedbackProcessorService::ProcessNullSessionFeedbacks();
            SalesReceiptService::processSalesToFeedbacksAtEndOfDay();
        })->description("Run Cron Checks")->dailyAt('23:30');

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
