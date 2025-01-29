<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends BaseModel
{
    use SoftDeletes;
    
    public function linkedTo() {
        return $this->belongsTo(Contact::class, 'linked_to');
    }
    
    public function assignedTo() {
        return $this->belongsTo(Contact::class, 'assigned_to');
    }
}
