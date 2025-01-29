<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Commons\Utils\Colors;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends BaseModel
{
    use SoftDeletes, Colors;
    
    public function events() {
        return $this->hasMany(CalendarEvent::class);
    }
    
    
}
