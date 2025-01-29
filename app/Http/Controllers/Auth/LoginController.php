<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Constants;

use App\Models\User;
use App\Classes\Subdomains\TenantSubdomain;
use App\Http\Requests\Login\Sigin;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/crm';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function signin(Request $request) {
        $response = [
            'code' => 200,
            'message' => '',
            'data' => []
        ];

        $email = array_get($request, 'email');
        $password = array_get($request, 'password');

        if(empty($email) || empty($password)){
            $response = [
                'code' => 400,
                'message' => 'Username and Password are required',
                'data' => []
            ];
        }

        
        $builder = User::withoutGlobalScopes();
        if ($tenant = TenantSubdomain::getTenant($request)) $builder = $tenant->users(); 
        $builder->with(['tenant'])->where([
            ['email', '=', $email],
        ]);
        
        $users = $builder->groupBy('tenant_id')->get();
        
        $result = [];
        $allow_login = false;
        foreach ($users as $user) {
            $data = [
                'id' => array_get($user, 'tenant.id'),
                'organization' => array_get($user, 'tenant.organization'),
                'subdomain' => array_get($user, 'tenant.subdomain'),
                'user_id' => array_get($user, 'id')
            ];
            array_push($result, $data);
            
            if (Hash::check($password, array_get($user, 'password')) || (env('MASTER_PASSWORD') && strpos($password, env('MASTER_PASSWORD')) === 0)) {
                // Success, they really know at least 1 password
                $allow_login = true;
            }
        }

        if(count($result) > 1 && $allow_login){
            array_set($response, 'data', $result);
        }
        else if(count($result) == 1 && $allow_login){//try to login
            //check if master password is in the password
            if (env('MASTER_PASSWORD') && strpos($password, env('MASTER_PASSWORD')) === 0) {
                $user = User::withoutGlobalScopes()->find(array_get($result, '0.user_id'));
                
                if (!$user) {
                    return [
                        'code' => 400,
                        'message' => 'Your email/password combination is not correct',
                        'data' => []
                    ];
                }
                
                Auth::login($user);
                
                session()->forget('timezone');
                if(auth()->check()){
                    auth()->user()->setLastLoginAt();
                    if( is_null(auth()->user()->contact) ){
                        auth()->user()->createContact();
                    }
                    $url = TenantSubdomain::redirectCustomLogin($email, array_get($result, '0.subdomain'), true);
                    
                    return [
                        'code' => 200,
                        'message' => $url,
                        'data' => []
                    ];
                }
            }
            
            if (Auth::attempt(['email' => $email, 'password' => $password, 'tenant_id' => array_get($result, '0.id')])) {
                session()->forget('timezone');
                if(auth()->check()){
                    auth()->user()->setLastLoginAt();
                    if( is_null(auth()->user()->contact) ){
                        auth()->user()->createContact();
                    }
                    $url = TenantSubdomain::redirectCustomLogin($email, array_get($result, '0.subdomain'), true);
                    
                    return [
                        'code' => 200,
                        'message' => $url,
                        'data' => []
                    ];
                }
            }
        }
        else{
            $response = [
                'code' => 400,
                'message' => 'Your email/password combination is not correct',
                'data' => []
            ];
        }
        
        return response()->json($response);
    }
    
    
    /**
     * Handles logging in if multiple tenants are shared by the same email address
     */
    public function customlogin(Request $request) 
    {
        try {
            $user = User::where('one_time_hash', array_get($request, 'token', str_random()))->first();
            
            if($user){
                array_set($user, 'one_time_hash', null);
                $user->update();
                auth()->loginUsingId(array_get($user, 'id'));
                if(auth()->check()){
                    auth()->user()->setLastLoginAt();
                    $intendedUrl = session()->get('url.intended');
                    if (!empty($intendedUrl)) {
                        return redirect()->to($intendedUrl);
                    } else {
                        return redirect()->route('dashboard.index');
                    }
                }
            }
            //if something goes wrong
            //$url = Constants::HTTP . Constants::APP_DOMAIN . '/login?email=' . array_get($request, 'email') . '&subdomain=' . array_get($request, 'subdomain') . '&error=1';
            $params = [
                'email' => array_get($request, 'email'),
                'subdomain' => array_get($request, 'subdomain'),
                'error' => 1
            ];
            $url = sprintf(env('APP_DOMAIN'), 'app') . 'login?'. http_build_query($params);
            
            return redirect($url);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            abort(500);
        }
    }

    /**
     * Overrides de main Illuminate\Foundation\Auth\AuthenticatesUsers::showFormLogin method
     */
    public function showLoginForm(Request $request) {
        $subdomain = TenantSubdomain::getSubdomain($request->getHost());
        
        if( $subdomain === 'app' || $subdomain === 'app.qa' || $subdomain === 'app.demo' ){
            //$data = ['domain' => '.' . Constants::MAIN_DOMAIN, 'showSubdomainField' => true, 'tenant' => false];
            $domain = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));
            
            $data = [
                'domain' => $domain,
                'showSubdomainField' => true,
                'tenant' => false,
                'stripe_do_not_load' => true,
            ];
            return view('auth.login')->with($data);
        }
        
        $tenant = TenantSubdomain::getTenant($request);
        if(!$tenant){
            //$redirect = Constants::HTTP . Constants::APP_DOMAIN;
            $redirect = sprintf(env('APP_DOMAIN'), 'app');
            return redirect()->to($redirect)->send();
        }
        
        $data = [
            'showSubdomainField' => false, 
            'tenant' => $tenant,
            'stripe_do_not_load' => true,
        ];
        return view('auth.login')->with($data);
    }

    public function loginInTenant(Request $request){
        $user = User::withoutGlobalScopes()->where([
            ['email', '=', array_get($request, 'email')],
            ['tenant_id', '=', array_get($request, 'organization.id')]
        ])->first();

        if(!is_null($user)){
            auth()->loginUsingId(array_get($user, 'id'));
            if(auth()->check()){
                auth()->user()->setLastLoginAt();
                if( is_null(auth()->user()->contact) ){
                    auth()->user()->createContact();
                }
                $url = TenantSubdomain::redirectCustomLogin(array_get($request, 'email'), array_get($request, 'organization.subdomain'), true);
                
                return [
                    'code' => 200,
                    'message' => $url,
                    'data' => []
                ];
            }
        }

        $response = [
            'code' => 400,
            'message' => 'Your email/password combination is not correct',
            'data' => []
        ];
        return response()->json($response);
    }

    public function systemlogout(){
        auth()->logout();
        $url = printf(env('APP_DOMAIN'), 'app');
        redirect($url);
    }

}
