<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Commands\Pledges\UpdatePromisedPayDate;

class UpdatePledgePayDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pledges:updatepaydate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the promised pay date if status = pledge';

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
        $update = new UpdatePromisedPayDate();
        $update->run();
        unset($update);
    }
}
