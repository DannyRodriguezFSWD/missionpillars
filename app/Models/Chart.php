<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chart extends BaseModel {
    
    protected $hidden = ['id', 'tenant_id', 'created_by', 'updated_by', 'created_by_session_id', 'updated_by_session_id', 'created_at', 'updated_at', 'deleted_at'];

    
}
