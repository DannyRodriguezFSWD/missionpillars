<?php

namespace App\Console\Commands;

use App\Classes\Email\CheckinAlert as Email;
use Illuminate\Console\Command;

class CheckinAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkin:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert event managers to checkin people';

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
    public function handle(Email $email)
    {
        $email->run();
    }
}
