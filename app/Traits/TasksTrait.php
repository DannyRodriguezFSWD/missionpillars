<?php

namespace App\Traits;

use App\Classes\Email\EmailQueue;
use App\Models\Task;

trait TasksTrait 
{
    public function emailAssignee($task)
    {
        if (!array_get($task, 'assignedTo.email_1')) {
            return false;
        }
        
        EmailQueue::set(array_get($task, 'assignedTo'), [
            'from_name' => array_get($task, 'assignedTo.tenant.organization'),
            'from_email' => array_get($task, 'assignedTo.tenant.email'),
            'subject' => 'New task received: '.array_get($task, 'name'),
            'content' => view('emails.send.tasks.assigned')->with(compact('task'))->render(),
            'model' => $task,
            'queued_by' => 'tasks.assigned'
        ]);
    }
}
