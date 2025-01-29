<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Classes\Subdomains\TenantSubdomain;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function showResetForm(Request $request, $token = null)
    {
        $tenant = TenantSubdomain::getTenant($request);
        $url = $request->getHost();
        $app = sprintf(env('APP_DOMAIN'), 'app');
        $tenant = null;
        if(strpos($app, $url) === false){//not main subdomain, we need to check custom subdomain
            $request = request();
            $tenant = TenantSubdomain::getTenant($request);
            if(!$tenant){//not a registered tenant
                abort(401);
            }
        }
        
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email, 'tenant' => $tenant]
        );
    }
}
