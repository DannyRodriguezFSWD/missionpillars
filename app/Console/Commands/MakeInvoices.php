<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Commands\Billing\Billing;

class MakeInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Billing for tenants';

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
        $billing = new Billing();
        $billing->run();
    }
}
