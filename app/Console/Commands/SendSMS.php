<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Twilio\TwilioSender;

class SendSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process SMS queue';

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
        $sender = new TwilioSender();
        $sender->run();
        unset($sender);
    }
}
