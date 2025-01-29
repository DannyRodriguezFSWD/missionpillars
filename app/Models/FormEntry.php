<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormEntry extends BaseModel
{
    use SoftDeletes;
    
    public function form() {
        return $this->belongsTo(Form::class);
    }
    
    public function contact() {
        //return $this->belongsTo(Contact::class);
        return $this->belongsToMany(Contact::class, 'contact_entry', 'form_entry_id', 'contact_id')->withPivot('relationship');
    }
    
    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function tenant(){
        return $this->belongsTo(Tenant::class);
    }
    
    public function getJsonValuesAttribute()
    {
        return json_decode($this->json, true);
    }
}
