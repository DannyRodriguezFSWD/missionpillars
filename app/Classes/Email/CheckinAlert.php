<?php

namespace App\Classes\Email;

use App\Models\CalendarEventTemplateSplit;
use App\Models\Contact;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class CheckinAlert 
{
    public function run()
    {
        $date = date('Y-m-d', strtotime('-1 days'));
        
        $events = CalendarEventTemplateSplit::withoutGlobalScopes()
                ->with(['template' => function ($query) {
                    $query->withoutGlobalScopes()
                            ->with(['group' => function ($query) {
                                $query->withoutGlobalScopes();
                            }])->with(['managers' => function ($query) {
                                $query->withoutGlobalScopes();
                            }]);
                }])->whereHas('template', function ($query) {
                    $query->withoutGlobalScopes()->whereNull('deleted_at')->where('remind_manager', 1);
                })->whereNull('deleted_at')->whereRaw("ifnull(end_date, start_date) like '$date%'")->get();
        
        foreach ($events as $event) {
            $this->email($event);
        }
    }
    
    public function email($event)
    {
        EmailQueue::set(array_get($event, 'template.managers.0'), [
            'from_name' => array_get($event, 'template.tenant.organization'),
            'from_email' => array_get($event, 'template.tenant.email'),
            'subject' => 'Checkin Reminder',
            'content' => view('emails.send.checkin.alert')->with(compact('event'))->render(),
            'model' => $event,
            'queued_by' => 'events.checkin.reminder'
        ]);
    }
}
