<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\ScheduleMonitor\ScheduleHealth;

use App\Schedule\ReminderSchedule;
use App\Schedule\CleanSchedule;
use App\Schedule\AuditSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // In staging
        // $schedule->call([new ReminderSchedule, 'remind_inventory'])->hourly();
        // $schedule->call([new ReminderSchedule, 'remind_low_capacity'])->dailyAt('01:30');
        // $schedule->call([new CleanSchedule, 'clean_history'])->dailyAt('01:00');
        // $schedule->call([new CleanSchedule, 'clean_deleted_inventory'])->dailyAt('02:00');
        // $schedule->call([new AuditSchedule, 'audit_error'])->weeklyOn(1, '3:00');
        // $schedule->call([new AuditSchedule, 'audit_dashboard'])->weeklyOn(2, '1:50');	
        // $schedule->command('dusk:run')->weeklyOn(6, '6:00');

        // In development
        // $schedule->command(ReminderSchedule::remind_inventory())->everyMinute();
        // $schedule->command(ReminderSchedule::remind_low_capacity())->everyMinute();
        // $schedule->command(CleanSchedule::clean_history())->everyMinute();
        // $schedule->command(CleanSchedule::clean_deleted_inventory())->everyMinute();
        // $schedule->command(AuditSchedule::audit_error())->everyMinute();
        // $schedule->command(AuditSchedule::audit_dashboard())->everyMinute();
        // $schedule->command('dusk:run')->everyMinute();
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
