<?php

namespace App\Console;

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
        Commands\RecalculateIncomes::class,
        Commands\CheckoutPropertyReservations::class,
        Commands\VerifyBudgetData::class,
        Commands\ShowBudgetDetails::class,
        Commands\FixBudgetJanuary::class,
        Commands\ClearBudgetData::class,
        Commands\DetectAndFixBudgetIssues::class,
        Commands\SendDailyReport::class,
        Commands\SendGuestNotifications::class,
        Commands\SendPaymentReminders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('reservations:checkout-property')->dailyAt('11:00');
        $schedule->command('inventory:check-low-stock')->daily();

        // Send daily report to management every morning at 7 AM
        $schedule->command('report:send-daily')->dailyAt('07:00');

        // Send automated guest notifications (pre-arrival, post-stay, birthday)
        $schedule->command('guests:send-notifications')->dailyAt('08:00');

        // Send payment reminders for unpaid bills every day at 9 AM
        $schedule->command('payments:send-reminders')->dailyAt('09:00');
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
