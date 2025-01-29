<?php

namespace App\Models;

use App\Observers\FormsObserver;
use App\Classes\MissionPillarsLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends BaseModel
{
    use SoftDeletes;
    
    public static function boot() {
        parent::boot();
        Form::observe(new FormsObserver());
    }
    
    public function entries() {
        return $this->hasMany(FormEntry::class);
    }
    
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->belongsToMany(Tag::class, 'form_tags');
    }
    
    public function groups() {
        return $this->belongsTo(Group::class);
    }
    
    public function events() {
        return $this->belongsTo(CalendarEvent::class);
    }
    
    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }
    
    public function chartOfAccount() {
        MissionPillarsLog::deprecated();
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }
    
    public function purpose() {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    /** Accessors **/
    
    public function getLabelsCollectionAttribute()
    {
        $formJson = collect(json_decode($this->json));
        
        $formLabelsCollection = $formJson->map(function ($field) {
            if (isset($field->name) && isset($field->label)) {
                return trim($field->label, '<br>');
            }
        })->filter()->values();
        
        return $formLabelsCollection;
    }
    
    public function getNamesCollectionAttribute()
    {
        $formJson = collect(json_decode($this->json));
        
        $formNamesCollection = $formJson->map(function ($field) {
            if (isset($field->name)) {
                return str_replace('[]', '', $field->name);
            }
        })->filter()->values();
        
        return $formNamesCollection;
    }
    
    public function getNameLabelCollectionAttribute()
    {
        $formJson = collect(json_decode($this->json));
        
        $nameLabelCollection = $formJson->mapWithKeys(function ($field) {
            if (isset($field->name) && isset($field->label)) {
                return [str_replace('[]', '', $field->name) => trim($field->label, '<br>')];
            } else {
                return [];
            }
        });
        
        return $nameLabelCollection;
    }
    
    public function getHasProfileImageAttribute()
    {
        return $this->form_json->contains('name', 'profile_image');
    }
    
    public function getRequiresProfileImageAttribute()
    {
        if ($this->has_profile_image) {
            return $this->form_json->where('name', 'profile_image')->where('required', true)->count() > 0;
        } else {
            return false;
        }
    }
    
    public function getFormJsonAttribute()
    {
        return collect(json_decode($this->json));
    }
    
    /** Accessors end **/
    
    public static function getFormsNamesAndLabels($forms = [])
    {
        if (empty($forms)) {
            $forms = self::whereNotNull('tenant_id')->get();
        }
        
        $formsNameLabels = collect([]);
        
        foreach ($forms as $form) {
            if ($form->nameLabelCollection) {
                $formsNameLabels = $formsNameLabels->merge($form->nameLabelCollection->toArray());
            }
        }
        
        return $formsNameLabels;
    }
}
