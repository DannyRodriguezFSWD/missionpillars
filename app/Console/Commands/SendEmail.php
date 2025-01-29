<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Email\Mailgun\Send as MailgunSender;

class SendEmail extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process email queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $mailgun = new MailgunSender();
        $mailgun->run();
        unset($mailgun);
    }

}
