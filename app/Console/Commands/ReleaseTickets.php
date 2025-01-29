<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Events\EventSignin;

class ReleaseTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Releases all reserved tikets did not finished user registration/payment';

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
        EventSignin::releaseTickets();
    }
}
