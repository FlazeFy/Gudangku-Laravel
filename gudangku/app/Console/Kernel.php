<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\ScheduleMonitor\ScheduleHealth;

use App\Schedule\ReminderSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // In staging
        $schedule->call([new ReminderSchedule, 'remind_inventory'])->hourly();

        // In development
        $schedule->command(ReminderSchedule::remind_inventory())->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
