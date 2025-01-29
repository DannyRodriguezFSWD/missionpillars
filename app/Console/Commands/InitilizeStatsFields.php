<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Shared\Tricks\TableDatetimeFieldsTrick;

class InitilizeStatsFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:initializefields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets datetime fields with random dates between today and last year';
    
    protected $updater;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TableDatetimeFieldsTrick $updater)
    {
        parent::__construct();
        $this->updater = $updater;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if( env('APP_ENVIROMENT') !== 'production' ){
            $this->updater->initialize();
        }
    }
}
