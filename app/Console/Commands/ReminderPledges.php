<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Email\Mailgun\PledgeEmailNotification;

class ReminderPledges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pledges:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a email reminder for contacts that have pledges';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reminder = new PledgeEmailNotification();
        $reminder->run();
        unset($reminder);
    }
}
