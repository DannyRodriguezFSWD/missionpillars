<?php

namespace App\Traits;

use \Illuminate\Contracts\Encryption\DecryptException;
use \Illuminate\Support\Facades\Crypt;
use App\Models\Tenant;
use App\Constants;

/**
 *
 * @author josemiguel
 */
trait Subdomains {

    public function getSubdomain($url) {
        $host = explode('.', $url);
        //$pieces = array_slice($host, 0, count($host) - 2);
        //$subdomain = implode('.', $pieces);
        $subdomain = $host[0];
        return $subdomain;
    }

    public function getTenant($subdomain = null) {
        $tenant = null;
        try {
            $id = Crypt::decrypt($subdomain);
            $tenant = Tenant::where('id', $id)->first();
        } catch (DecryptException $e) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        }
        return $tenant;
        
    }
    
    public function redirectCustomLogin($email, $subdomain) {
        $uuid = \Ramsey\Uuid\Uuid::uuid1();
        auth()->user()->one_time_hash = $uuid;
        auth()->user()->update();
        auth()->logout();
        $url = Constants::HTTP . $subdomain . '.' . Constants::MAIN_DOMAIN . 
                '/customlogin?token=' . $uuid . 
                '&email=' . $email . 
                '&subdomain=' . $subdomain;
        return redirect($url);
    }
}
