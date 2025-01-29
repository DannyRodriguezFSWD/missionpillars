<?php
namespace App\Classes\Commands\Oauth;

use App\Models\TransactionTemplate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\OauthAccessToken;
use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Models\User;
/**
 * Description of UpdatePrimisedPayDate
 *
 * @author josemiguel
 */
class OauthRenewTokens {
    public function run(){
        $tokens = OauthAccessToken::withoutGlobalScopes()->where(function($query){
            $query->where([
                ['revoked', '=', false]
            ])->whereBetween('expires_at',[
                Carbon::now()->startOfDay(), Carbon::now()->addWeeks(1)->endOfDay()
            ]);
        })->orWhere(function($query){
            $query->where([
                ['revoked', '=', false],
                ['expires_at', '<', Carbon::now()]
            ]);
        })->get();
        
        if(count($tokens) > 0){
            foreach($tokens as $token){
                $user = User::withoutGlobalScopes()->where('id', array_get($token, 'user_id'))->first();
                auth()->setUser($user);
                
                if(auth()->check()){
                    array_set($token, 'revoked', true);
                    $token->update();

                    $tokenName = sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain'), env('APP_DOMAIN'));
                    $result = [];
                    array_set($result, 'subdomain', auth()->user()->tenant->subdomain);
                    array_set($result, 'system_name', env('APP_INTERNAL_NAME'));
                    $token = auth()->user()->createToken($tokenName);
                    auth()->user()->tenant->setToken($token, $tokenName);
                    array_set($result, 'requestor_system_api_key', $token->accessToken);
                    $integration = auth()->user()->tenant->integrations()->where([
                        ['service', '=', 'Continue to Give']
                    ])->first();
                    $value = $integration->values()->where('key', 'API_KEY')->first();
                    
                    $c2g = new ContinueToGiveIntegration();
                    $response = $c2g->returnToken(array_get($value, 'value'), $result);
                    auth()->logout();
                }
            }
        }
    }
}