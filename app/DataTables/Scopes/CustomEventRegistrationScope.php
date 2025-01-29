<?php

namespace App\DataTables\Scopes;

use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;

class CustomEventRegistrationScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $event_registration = array_get($this->request, 'search.event_registration');
        $event_registration_paid = array_get($this->request, 'search.event_registration_paid');
        $event_registration_checked_in = array_get($this->request, 'search.event_registration_checked_in');
        $event_registration_released_ticket = array_get($this->request, 'search.event_registration_released_ticket');
        if ($event_registration) {
            $regs = '';
            foreach ($event_registration as $reg){
                $regs .= ','.$reg;
            }
            $splits_id = CalendarEventTemplateSplit::whereIn('calendar_event_template_id',array_filter(explode(',',$regs)))->get(['id'])->pluck('id')->toArray();
            $query->whereHas('eventRegistered', function ($query) use ($splits_id, $event_registration_paid, $event_registration_released_ticket) {
                $query->whereIn('calendar_event_template_split_id', $splits_id);
                if ($event_registration_paid) {
                    $query->where('paid', true);
                }
                if ($event_registration_released_ticket) {
                    $query->whereHas('releasedTickets');
                }
            });
            if ($event_registration_released_ticket) {
                $query->whereDoesntHave('eventRegistered', function ($query) use ($splits_id) {
                    $query->whereIn('calendar_event_template_split_id', $splits_id)->whereHas('tickets');
                });
            }
            if ($event_registration_checked_in) $query->whereHas('checkedIn');
        }
        return $query;
    }
}
