<?php

namespace App\Http\Controllers;

use App\Jobs\RegistrationDataSync;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOneClickRegister;
use App\Constants;
use App\Traits\OneClickRegister;
use App\Classes\Transactions;
use App\Traits\AlternativeIdTrait;
use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Classes\ContinueToGive\ContinueToGiveCampaigns;
use App\Classes\ContinueToGive\ContinueToGiveMissionaries;
use App\Models\Address;
use App\Models\Module;
use App\Models\Tenant;
use App\Classes\Salesmate\Salesmate;
use App\Traits\ModuleTrait;
use Illuminate\Support\Str;

class OneClickController extends Controller {

    use OneClickRegister,
        AlternativeIdTrait,
        ModuleTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $params = $request->all();
        //dd($params);
        $user = $this->getUserByHash($params);

        if ( !is_null($user) ) {
            auth()->loginUsingId(array_get($user, 'id'));
            if(auth()->check()){
                auth()->user()->setLastLoginAt();
            }
        } else {
            abort(401, 'Unauthorized.');
        }

        if (array_has($params, 'one_time_token')) {//one click register
            $data = $this->getExternaldata($params);
            if( env('API_RETURN_TOKEN') ){
                $result = $this->generateToken($data);
                $c2g = new ContinueToGiveIntegration();
                $c2g->returnToken(array_get($data, 'data.api_key'), $result);
            }
            else if(env('APP_MODE') === 'developer'){
                $this->oneClickSystemIntegration($data);
            }
        }

        if (array_has($params, 'action') && array_get($params, 'action') === 'login') {
            $reference = array_get($params, 'reference');
            switch ($reference) {
                case 'contacts':
                    $redirection = redirect()->route('contacts.index');
                    break;
                case 'transactions':
                    $redirection = redirect()->route('transactions.index');
                    break;
                case 'crmreports':
                    $redirection = redirect()->route('crmreports.index');
                    break;
                case 'form_builder':
                    $redirection = redirect()->route('forms.index');
                    break;
                case 'massemail':
                    $redirection = redirect()->route('communications.index');
                    break;
                case 'contributions':
                    $redirection = redirect()->route('print-mail.index');
                    break;
                case 'events':
                    $redirection = redirect()->route('events.index');
                    break;
                case 'groups':
                    $redirection = redirect()->route('groups.index');
                    break;
                case 'pledges':
                    $redirection = redirect()->route('pledges.index');
                    break;
                case 'reports':
                    $redirection = redirect()->route('crmreports.index');
                    break;
                case 'childcheckin':
                    $redirection = redirect()->route('child-checkin.about');
                    break;
                case 'accounting_coas':
                    $redirection = redirect()->route('accounts.index');
                    break;
                case 'accounting_bank_integrations':
                    $redirection = redirect()->route('bank-accounts.index');
                    break;
                case 'accounting_transactions':
                    $redirection = redirect()->route('registers.index');
                    break;
                case 'accounting_fund_transfers':
                    $redirection = redirect()->route('journal-entries.fund-transfers');
                    break;
                case 'accounting_reports':
                    $redirection = redirect()->route('accounting.reports.index');
                    break;
                default:
                    $redirection = redirect()->route('dashboard.index');
                    break;
            }
            //return redirect()->route('dashboard.index');
            return $redirection;
        }

        /* if params has id and api_key, its one click register else form registration */
        if (array_has($params, 'one_time_token')) {//one click register
            $data = $this->getExternaldata($params);

            $salesmate = new Salesmate();
            $salesmate->missionPillarsSignup(auth()->user());
            
            $contact = $this->oneClickRegisterContact($data);
            $id = $this->oneClickRegisterAddress($data, $contact);
            if ($id) {
                $fields = [
                    'alt_id' => array_get($data, 'data.contact-address_alt_id'),
                    'label' => auth()->user()->name,
                    'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
                ];
                $this->alternativeIdCreate($id, Address::class, $fields);
            }

            if (!$request->has('readonly')) {
                RegistrationDataSync::dispatch(array_get($data,'data.api_key'),auth()->user());
            }
            return redirect()->route('newMpAccount');
        } else { //form register
            $contact = $this->oneClickRegisterContact(null);
        }

        return redirect()->route('dashboard.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if ($request->has('one_time_token')) {
            $externalData = $this->getExternaldata($request->all());

            $tenant = $this->oneClickRegisterTenant($externalData, true);
            $domain = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));

            if ($tenant) {
                $user = $this->getUserByAltId($externalData, $tenant);
                if (!$user) {//register
                    $data = ['data' => $externalData, 'domain' => $domain, 'readonly' => true, 'tenant' => $tenant];
                    return view('oneclick.register')->with($data);
                }
                //login
                $params = $request->all();
                array_set($params, 'one_time_hash', array_get($user, 'one_time_hash'));
                array_set($params, 'action', 'login');

                $url = sprintf(env('APP_DOMAIN'), array_get($user->tenant, 'subdomain')).'oneclick?';
                $url .= http_build_query($params);

                return redirect($url);
            }

            $data = ['data' => $externalData, 'domain' => $domain, 'readonly' => false];

            return view('oneclick.register')->with($data);
        }
        abort(401);
    }

    /**
     * Create same array structure as a registered user
     * @param mixed $externalData
     * @return array
     */
    private function getFakeUser($externalData) {
        return [
            'source' => 'ctg',
            'id' => array_get($externalData, 'data.contact_alt_id'),
            'name' => array_get($externalData, 'data.contact_first_name'. array_get($externalData, 'contact_preferred_name')),
            'last_name' => array_get($externalData, 'data.contact_last_name'),
            'email' => array_get($externalData, 'data.contact_email_1'),
            'tenant' => [
                'id' => array_get($externalData, 'data.organization_alt_id'),
                'organization' => array_get($externalData, 'data.organization_name')
            ],
            'contact' => [
                'cell_phone' => '',
                'preferred_name' => array_get($externalData, 'data.contact_preferred_name'),
                'addressInstance' => [
                    [
                        'mailing_address_1' => array_get($externalData, 'data.contact-address_address'),
                        'city' => array_get($externalData, 'data.contact-address_city'),
                        'region' => array_get($externalData, 'data.contact-address_region'),
                        'postal_code' => array_get($externalData, 'data.contact-address_postal_code'),
                        'country' => array_get($externalData, 'data.contact-address_country')
                    ]
                ]
            ]
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StoreOneClickRegister  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOneClickRegister $request) {
        if (!$request->has('readonly')) {
            $request->validate(['subdomain' => 'unique:tenants']);
        }
        
        $data = $this->getExternaldata($request->all());
        array_set($data, 'data.contact_first_name', array_get($request, 'name'));
        array_set($data, 'data.contact_last_name', array_get($request, 'lastname'));
        array_set($data, 'data.contact_email_1', array_get($request, 'email'));
        array_set($data, 'data.subdomain', array_get($request, 'subdomain'));
        array_set($data, 'data.password', Str::random(16));
        array_set($data, 'data.ein', array_get($request, 'ein'));

        if (empty(array_get($data, 'data.contact_first_name'))) {
            if (empty(array_get($request, 'preferred_name'))) {
                array_set($data, 'data.contact_first_name', 'Admin');
            } else {
                array_set($data, 'data.contact_first_name', array_get($request, 'preferred_name'));
            }
        }
        
        $tenant = $this->oneClickRegisterTenant($data);

        if(!is_null($tenant) && $tenant instanceof Tenant){
            $tenant->upgrade($request);
            
            // Here we activate the trial period for the CRM module
            $crmModuleId = 2;
            $crmModule = $tenant->modulesWithTrashed()->where('id', $crmModuleId)->first();
            if (is_null($crmModule)) {
                if (array_get($tenant, 'type') === 'missionary') {
                    array_set($request, 'promo_code', 'missionarycrm2020');
                }
                
                $tenant->enableModule($crmModuleId, $request, $tenant);
            }
        }

        if ($tenant instanceof \Illuminate\Database\QueryException) {
            return redirect()->route('subdomain');
        }

        $result = $this->oneClickRegisterUser($data, $tenant);
        $user = array_get($result, 'user');
        $action = array_get($result, 'action');
        $params = [];
        array_set($params, 'one_time_token', array_get($request, 'one_time_token'));
        array_set($params, 'one_time_hash', array_get($user, 'one_time_hash'));
        array_set($params, 'action', $action);
        if ($request->has('readonly')) {
            array_set($params, 'readonly', array_get($request, 'readonly'));
        }

        $url = sprintf(env('APP_DOMAIN'), array_get($user->tenant, 'subdomain')).'oneclick?';
        $url .= http_build_query($params);
        return redirect($url);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    /**
     * @todo send API token to c2g
     * @param type $token
     */
    public function oneClickSetup() {
        $integration = auth()->user()->tenant->integrations->where('service', 'Continue to Give')->first();
        if ($integration) {
            $value = $integration->values->where('key', 'API_KEY')->first();
            if ($value) {
                $transactions = new Transactions($value->value);
                $transactions->executeTransactions();
            }
        }
        return redirect()->route('dashboard.index');
    }

}
