<?php

namespace App\Console\Commands;

use App\Classes\Email\TaskDueAlert as Email;
use Illuminate\Console\Command;

class TaskDueAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert users when task is near due date';

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
