<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendEmail;
use App\Console\Commands\SendSMS;
use App\Console\Commands\UpdateStatsFields;
use App\Console\Commands\InitilizeStatsFields;
use App\Console\Commands\ReminderPledges;
use App\Console\Commands\UpdatePledgePayDate;
use App\Console\Commands\MakeInvoices;
use App\Classes\MissionPillarsLog;
use App\Console\Commands\RenewTokens;
use App\Console\Commands\ReleaseTickets;
use App\Console\Commands\CheckinAlert;
use App\Console\Commands\CheckTrialAccounts;
use App\Console\Commands\TaskDueAlert;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SendEmail::class,
        UpdateStatsFields::class,
        InitilizeStatsFields::class,
        ReminderPledges::class,
        UpdatePledgePayDate::class,
        SendSMS::class,
        MakeInvoices::class,
        RenewTokens::class,
        ReleaseTickets::class,
        CheckinAlert::class,
        CheckTrialAccounts::class,
        TaskDueAlert::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //emails tasks
        if( env('APP_SEND_EMAILS', false) ){
            if( env('APP_LOG_RUNNING_CRON_JOBS', false) ){
                MissionPillarsLog::log([
                    'event' => 'APP_SEND_EMAILS',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'message' => 'APP_SEND_EMAILS is running'
                ]);
            }
            $schedule->command('email:send')->everyMinute()->withoutOverlapping();
            $schedule->command('pledges:reminder')->daily()->withoutOverlapping();
            
            if (env('APP_SEND_CHECKIN_REMINDER', false)) {
                $schedule->command('checkin:alert')->dailyAt('15:00')->withoutOverlapping();
            }
            
            if (env('APP_SEND_TASK_DUE', false)) {
                $schedule->command('task:due')->dailyAt('15:10')->withoutOverlapping();
            }
        }

        if( env('APP_SEND_SMS', false) ){
            if( env('APP_LOG_RUNNING_CRON_JOBS', false) ){
                MissionPillarsLog::log([
                    'event' => 'APP_SEND_SMS',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'message' => 'APP_SEND_SMS is running'
                ]);
            }
            $schedule->command('sms:send')->everyMinute()->withoutOverlapping();
        }

        if( env('APP_UPDATE_PLEDGE_DATES', false) ){
            $schedule->command('pledges:updatepaydate')->dailyAt('00:30')->withoutOverlapping();
        }

        //dev tasks
        if( env('APP_ENVIROMENT') !== 'production' ){
            $schedule->command('stats:updatefields')->daily()->withoutOverlapping();
        }
        // $schedule->command('inspire')
        //          ->hourly();

        if( env('APP_MAKE_INVOICES') == true ){
            $schedule->command('invoices:make')->monthlyOn(date('t'), '23:00')->withoutOverlapping();
        }

        if(env('APP_TICKETS_RELEASE', false)){
            $schedule->command('tickets:release')->everyMinute()->withoutOverlapping();
            if( env('APP_LOG_RUNNING_CRON_JOBS', false) ){
                MissionPillarsLog::log([
                    'event' => 'APP_TICKETS_RELEASE',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'message' => 'APP_TICKETS_RELEASE executed'
                ]);
            }
        }

        if(env('APP_ENABLE_TOKEN_RENEW', false)){
            $schedule->command('tokens:renew')->daily()->withoutOverlapping();
            if( env('APP_LOG_RUNNING_CRON_JOBS', false) ){
                MissionPillarsLog::log([
                    'event' => 'APP_ENABLE_TOKEN_RENEW',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'message' => 'APP_ENABLE_TOKEN_RENEW executed'
                ]);
            }
        }
        
        if (env('APP_CHECK_TRIAL_ACCOUNTS', false)) {
            $schedule->command('check:trial')->dailyAt('22:00')->withoutOverlapping();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
