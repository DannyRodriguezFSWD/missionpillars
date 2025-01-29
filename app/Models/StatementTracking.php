<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatementTracking extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'statement_tracking';
    
    public function contacts() {
        return $this->belongsToMany(Contact::class, 'contact_statement_tracking')->withTimestamps();
    }
}
