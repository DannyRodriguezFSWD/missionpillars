<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dashboard extends BaseModel
{
    use SoftDeletes;
    protected $table = 'dashboard';
    
    public function widgets() {
        return $this->hasMany(Widget::class);
    }
}
