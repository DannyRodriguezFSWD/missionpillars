<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MPLog extends Model
{
    protected $table = 'logs';
    protected $guarded = ['id'];
}
