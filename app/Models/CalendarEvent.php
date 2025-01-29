<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\CalendarEventsObserver;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends BaseModel {
    use SoftDeletes;
    
    protected $table = 'calendar_event_templates';

    public static function boot() {
        parent::boot();
        CalendarEvent::observe(new CalendarEventsObserver());
    }
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
    
    public function calendar() {
        return $this->belongsTo(Calendar::class);
    }
    
    public function repetitions() {
        return $this->hasMany(CalendarEvent::class, 'parent_calendar_event_id', 'id');
    }
    
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->belongsToMany(Tag::class, 'calendar_event_tags');
    }
    
    public function forms() {
        return $this->belongsToMany(Form::class, 'calendar_event_forms');
    }
    
    public function form() {
        return $this->belongsTo(Form::class);
    }
    
    
    public function linkedForm() {
        return $this->belongsTo(Form::class, 'form_id')->whereNotNull('tenant_id');
    }

    public function ticketOptions(){
        return $this->hasMany(TicketOption::class)->where('show_ticket', true);
    }

    public function managers(){
        return $this->belongsToMany(Contact::class, 'calendar_event_manager');
    }
    
    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }
    
    public function chartOfAccount() {
        return $this->belongsTo(Purpose::class,'purpose_id');
    }
    
    public function splits() {
        return $this->hasMany(CalendarEventTemplateSplit::class, 'calendar_event_template_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
