<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Commands\Oauth\OauthRenewTokens;

class RenewTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew tokens expiration date';

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
        $oauth = new OauthRenewTokens();
        
        $oauth->run();
    }
}
