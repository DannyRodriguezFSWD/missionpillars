<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unsubscribe extends BaseModel
{
    protected $table = 'unsubscribed';
    
    public function list()
    {
        return $this->belongsTo(Lists::class);
    }
}
