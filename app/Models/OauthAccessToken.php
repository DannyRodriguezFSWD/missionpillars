<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends BaseModel
{
    public $incrementing = false;
    
    public function scopeNotRevoked($query)
    {
        return $query->where('revoked',false);
    }
}
