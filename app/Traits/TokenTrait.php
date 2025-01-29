<?php

namespace App\Traits;

use App\Models\TenantToken;
use App\Traits\AlternativeIdTrait;
use Illuminate\Support\Facades\DB;
use App\Models\OauthAccessToken;

/**
 *
 * @author josemiguel
 */
trait TokenTrait {
    use AlternativeIdTrait;
    
    public function setToken($token, $name = 'system') {
        $key = OauthAccessToken::findOrFail($token->token->id);
        array_set($key, 'tenant_id', auth()->user()->tenant->id);
        array_set($key, 'token', $token->accessToken);
        $key->update();
        
    }
    
    public function tokenExists($name) {
        
        return OauthAccessToken::where('name', $name)
        ->notRevoked()
        ->orderBy('expires_at', 'DESC')
        ->first();
    }

}
