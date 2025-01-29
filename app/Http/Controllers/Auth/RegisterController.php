<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Classes\Subdomains\TenantSubdomain;
use App\Constants;
use App\Traits\Subdomains;
use App\Http\Requests\Login\Register;
use App\Models\Module;

class RegisterController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Register Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users as well as their
      | validation and creation. By default this controller uses a trait to
      | provide this functionality without requiring any additional code.
      |
     */

use RegistersUsers;
    use Subdomains;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $registerView = 'auth.register';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
                    'name' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users',
                    'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data) {

        return User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Overrides default Illuminate\Foundation\Auth\RegistersUsers::showRegistrationForm
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm(Request $request) {
        $subdomain = TenantSubdomain::getSubdomain($request->getHost());
        if( $subdomain === 'app' || $subdomain === 'app.qa' || $subdomain === 'app.demo' ){
            $domain = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));
            $data = [
                'domain' => '.' . $domain,
                'showSubdomainField' => true,
                'tenant' => false,
                'subdomain' => $subdomain,
                'stripe_do_not_load' => true,
            ];
            return view('auth.register')->with($data);
        }
        $tenant = TenantSubdomain::getTenant($request);
        if(!$tenant){
            $redirect = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));
            return redirect()->to($redirect)->send();
        }
        
        $data = [
            'showSubdomainField' => false, 
            'tenant' => $tenant,
            'stripe_do_not_load' => true,
        ];
        return view('auth.register')->with($data);
    }
    
    public function customRegister(Register $request) {
        $verifyCaptcha = verifyCaptcha(array_get($request, 'g-recaptcha-response'));
        if (!$verifyCaptcha->success) {
            return redirect()->back()->withInput($request->all())->withErrors(['recaptcha' => 'Please check the recaptcha box']);
        }
        
        $tenant = (int) $request->isSubdomain === 0 ? TenantSubdomain::newTenant($request) : TenantSubdomain::getTenant($request);

        if(!is_null($tenant)){
            $tenant->upgrade($request);
        }

        return TenantSubdomain::registerNewTenantUser($tenant, $request);
    }

}
