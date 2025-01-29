<?php

namespace App\Classes\Email;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskDueAlert 
{
    public function run()
    {
        $tasks = Task::withoutGlobalScopes()->with(['assignedTo' => function ($query) {
            $query->withoutGlobalScopes();
        }])->with(['linkedTo' => function ($query) {
            $query->withoutGlobalScopes();
        }])->where('email_due', date('Y-m-d'))->where('email_due_sent', 0)->get();
        
        foreach ($tasks as $task) {
            $this->email($task);
            DB::table('tasks')->where('id', array_get($task, 'id'))->update(['email_due_sent' => 1]);
        }
    }
    
    public function email($task)
    {
        EmailQueue::set(array_get($task, 'assignedTo'), [
            'from_name' => array_get($task, 'assignedTo.tenant.organization'),
            'from_email' => array_get($task, 'assignedTo.tenant.email'),
            'subject' => 'Reminder task is due soon: '.array_get($task, 'name'),
            'content' => view('emails.send.tasks.due')->with(compact('task'))->render(),
            'model' => $task,
            'queued_by' => 'tasks.due'
        ]);
    }
}
