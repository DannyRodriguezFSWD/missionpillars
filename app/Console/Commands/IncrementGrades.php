<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IncrementGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'increment:grades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increase grade field by 1 for each contact';

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
        // set all 12 grades to null
        DB::statement("update contacts set grade = null where grade = 12");
        
        // increment all grades by 1
        DB::statement("update contacts set grade = grade + 1 where grade between 1 and 11");
        
        // change k grade to 1
        DB::statement("update contacts set grade = 1 where grade = 'K'");
        
        // change p grade to k
        DB::statement("update contacts set grade = 'K' where grade = 'P'");
    }
}
