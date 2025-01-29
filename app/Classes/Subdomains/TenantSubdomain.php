<?php

namespace App\Classes\Subdomains;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Constants;

/**
 * Description of Subdomains
 *
 * @author josemiguel
 */
class TenantSubdomain {

    private static $subdomain = null;

    public static function getSubdomain($url) {
        $host = explode('.', $url);
        $subdomain = $host[0];
        return $subdomain;
    }

    public static function getTenant(Request $request, $subdomain = null) {
        self::$subdomain = self::getSubdomain($request->getHost());
        if ($subdomain) {
            self::$subdomain = $subdomain;
        }
        $tenant = Tenant::where('subdomain', self::$subdomain)->first();
        return $tenant;
    }

    public static function redirectCustomLogin($email, $subdomain, $url_only = false) {
        $uuid = \Ramsey\Uuid\Uuid::uuid1()->toString();
        auth()->user()->one_time_hash = $uuid;
        auth()->user()->update();
        auth()->logout();
        /*
          $url = Constants::HTTP . $subdomain . '.' . Constants::MAIN_DOMAIN .
          '/customlogin?token=' . $uuid .
          '&email=' . $email .
          '&subdomain=' . $subdomain;
         * 
         */
        $data = [
            'token' => $uuid,
            'email' => $email,
            'subdomain' => $subdomain
        ];
        $url = sprintf(env('APP_DOMAIN'), $subdomain) . 'customlogin?';
        $url .= http_build_query($data);
        if($url_only){
            return $url;
        }
        return redirect($url);
    }

    /**
     * NOTE This seems an odd place for this functionality and is re-implemented in OneClickRegister
     */
    public static function newTenant($request) {
        $tenant = new Tenant();
        array_set($tenant, 'organization', array_get($request, 'organization'));
        array_set($tenant, 'first_name', array_get($request, 'name'));
        array_set($tenant, 'last_name', array_get($request, 'lastname'));
        array_set($tenant, 'subdomain', array_get($request, 'subdomain'));
        array_set($tenant, 'phone', array_get($request, 'phone'));
        array_set($tenant, 'email', array_get($request, 'email'));
        array_set($tenant, 'website', array_get($request, 'website'));
        if ($tenant->save()) {
            return $tenant;
        }
        return null;
    }

    /**
     * @todo redirect to right tenant before register contact
     * @param type $tenant
     * @param type $request
     * @return type
     */
    public static function registerNewTenantUser($tenant, $request) {
        if ($tenant) {
            $user = User::withoutGlobalScopes()->where([
                        ['email', '=', array_get($request, 'email')],
                        ['tenant_id', '=', array_get($tenant, 'id')]
                    ])->whereNull('deleted_at')->first();

            if ($user) {
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                            'email' => 'required|unique:users'
                ]);
                return redirect()->back()->withInput($request->all())->withErrors($validator);
            }

            $user = self::newTenantUser($tenant, $request);
            if ($user) {
                $role = (string) $request->isSubdomain === '0' ? $role = Role::where('name', 'organization-owner')->first() : $role = Role::where('name', 'organization-contact')->first();
                $user->roles()->save($role);
                $one_time_hash = array_get($user, 'one_time_hash');
                //$url = str_replace('[:subdomain:]', array_get($tenant, 'subdomain'), Constants::SUBDOMAIN_REDIRECT_URL);
                $url = sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain', 'app')) . 'oneclick?';
                $url .= http_build_query(['one_time_hash' => $one_time_hash]);
                return redirect($url);
            }
        }
        abort(500);
    }

    public static function newTenantUser($tenant, $request) {
        $user = new User();
        array_set($user, 'name', array_get($request, 'name'));
        array_set($user, 'last_name', array_get($request, 'lastname'));
        array_set($user, 'email', array_get($request, 'email'));
        array_set($user, 'password', bcrypt(array_get($request, 'password')));
        array_set($user, 'one_time_hash', md5(time()));
        if ($tenant->users()->save($user)) {
            return $user;
        }
        return null;
    }

}
