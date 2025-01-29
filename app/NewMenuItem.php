<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NewMenuItem extends Model
{
    public function scopeNotEndedYet($query)
    {
        return $query->where('end_at', '>', Carbon::now());
    }
}
