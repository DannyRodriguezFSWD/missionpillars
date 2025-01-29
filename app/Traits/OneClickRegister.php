<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Contact;
use App\Models\AltId;
use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Constants;
use App\Models\Role;
use App\Models\Integration;
use App\Models\IntegrationValue;
use Illuminate\Support\Facades\DB;

/**
 *
 * @author josemiguel
 */
trait OneClickRegister {

    /**
     * Retrieves user avoiding tenant_id check (this is required this way because its just registered user in app domain)
     * @param Array $data
     * @return App\Model\User
     */
    public function getUserByAltId($data, $tenant = null) {
        $altId = AltId::withoutGlobalScopes()->where([
                    ['alt_id', '=', array_get($data, 'data.contact_alt_id')],
                    ['relation_type', '=', User::class],
                    ['tenant_id', '=', array_get($tenant, 'id')]
                ])->first();
        if ($altId) {
            $user = $altId->getRelationTypeInstance;

            if ($user) {
                DB::table('users')
                        ->where('id', array_get($user, 'id'))
                        ->update(['one_time_hash' => md5(time())]);
            }
            $user = User::withoutGlobalScopes()->where([
                        ['id', '=', array_get($user, 'id')]
                    ])->first();

            return $user;
        }
        return null;
    }

    /**
     * Register a new user if does'n exists and assigns role.
     * Determines if its a new user register or login (if user exists then login else register)
     * @param Array $data
     * @param App\Models\Tenant $tenant
     * @return mixed
     */
    public function oneClickRegisterUser($data, $tenant = null) {
        $action = 'login';
        $user = $this->getUserByAltId($data, $tenant);
        if (!$user) {
            $action = 'register';
            $password = array_get($data, 'data.password');
            $user = new User();
            array_set($user, 'name', array_get($data, 'data.contact_first_name'));
            array_set($user, 'last_name', array_get($data, 'data.contact_last_name'));
            array_set($user, 'email', array_get($data, 'data.contact_email_1'));
            array_set($user, 'password', bcrypt($password));
            array_set($user, 'one_time_hash', md5(time()));
            if ($tenant->users()->save($user)) {
                $role = Role::where(['name' => 'organization-owner'])->first();
                $user->roles()->save($role);

                DB::table('alt_ids')->insert([
                    [
                        'tenant_id' => array_get($tenant, 'id'),
                        'alt_id' => array_get($data, 'data.contact_alt_id'),
                        'relation_id' => array_get($user, 'id'),
                        'relation_type' => get_class($user),
                        'label' => array_get($user, 'name'),
                        'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]
                ]);
            }
        }

        return ['user' => $user, 'action' => $action];
    }

    /**
     * Register a new tenant if doesn't exists
     * NOTE This functionality is re-implemented in TenantSubdomain
     * @param Array $data
     * @return \Illuminate\Database\QueryException|App\Models\Tenant
     */
    public function oneClickRegisterTenant($data, $getOnly = false) {
        $altId = AltId::withoutGlobalScopes()->where([
                    ['alt_id', '=', array_get($data, 'data.organization_alt_id')],
                    ['relation_type', '=', Tenant::class]
                ])->first();
        //if tenant exists return existing tenant
        if ($altId) {
            return $altId->getRelationTypeInstance;
            //$tenant = Tenant::findOrFail(array_get($altId, 'relation_id'));
            //return $tenant;
        }

        if (!$getOnly) {
            $tenant = new Tenant();
            array_set($tenant, 'organization', array_get($data, 'data.organization_name'));
            array_set($tenant, 'first_name', array_get($data, 'data.contact_first_name'));
            array_set($tenant, 'last_name', array_get($data, 'data.contact_last_name'));

            $subdomain = str_slug(trim(array_get($data, 'data.subdomain')));
            array_set($tenant, 'subdomain', $subdomain);

            array_set($tenant, 'phone', array_get($data, 'data.contact_cell_phone'));
            array_set($tenant, 'email', array_get($data, 'data.contact_email_1'));
            array_set($tenant, 'type', array_get($data, 'data.organization_type'));
            array_set($tenant, 'ein', array_get($data, 'data.ein'));
            try {
                if ($tenant->save()) {
                    DB::table('alt_ids')->insert([
                        [
                            'tenant_id' => array_get($tenant, 'id'),
                            'alt_id' => array_get($data, 'data.organization_alt_id'),
                            'relation_id' => array_get($tenant, 'id'),
                            'relation_type' => get_class($tenant),
                            'label' => array_get($tenant, 'organization'),
                            'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                        ]
                    ]);
                }
                return $tenant;
            } catch (\Illuminate\Database\QueryException $ex) {
                return $ex;
            }
        }
        return null;
    }

    /**
     * 
     * @param Array $params
     * @return Array|abort
     */
    private function getExternaldata($params) {
        $id = array_get($params, 'one_time_token');
        $c2g = new ContinueToGiveIntegration();
        $data = $c2g->getSingleSignOnData($id);

        if (!$data) {
            //abort(500);
            return [];
        }

        if (array_key_exists('status_code', $data)) {
            abort(array_get($data, 'status_code'), array_get($data, 'message'));
        }

        return $data;
    }

    /**
     * Register new contact if doesn't exist
     * @param App\Models\User $user
     * @param Array $data
     * @return App\Models\Contact|null
     */
    public function oneClickRegisterContact($data) {

        $altId = AltId::withoutGlobalScopes()->where([
                    ['alt_id', '=', array_get($data, 'data.contact_alt_id')],
                    ['relation_type', '=', Contact::class],
                    ['tenant_id', '=', auth()->user()->tenant->id]
                ])->first();

        if ($altId) {
            return $altId->getRelationTypeInstance;
        }

        $contact = auth()->user()->contact ?: auth()->user()->createContact();
        array_set($contact, 'user_id', array_get(auth()->user(), 'id'));
        if (auth()->user()->tenant->contacts()->save($contact)) {

            $alt = array_get($data, 'data.contact_alt_id');
            if (!$data) {
                $alt = array_get(auth()->user(), 'id');
            }

            $class = \App\Models\Contact::class;
            $altId = $this->alternativeIdRetrieve($alt, $class);
            if (!$altId) {
                $fields = ['alt_id' => $alt, 'label' => auth()->user()->name, 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                $this->alternativeIdCreate($contact->id, $class, $fields);
                return $contact;
            }
        }
        return null;
    }

    public function oneClickRegisterAddress($data, $contact) {
        if ($data) {
            $insert = [
                'is_primary' => array_get($data, 'data.contact-address_is_primary'),
                'country' => array_get($data, 'data.contact-address_country'),
                'region' => array_get($data, 'data.contact-address_region'),
                'city' => array_get($data, 'data.contact-address_city'),
                'mailing_address_1' => array_get($data, 'data.contact-address_address'),
                'postal_code' => array_get($data, 'data.contact-address_postal_code'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => get_class($contact),
                'tenant_id' => array_get($contact, 'tenant_id')
            ];

            $id = DB::table('addresses')->insertGetId($insert);

            return $id;
        }
        return null;
    }

    /**
     * Register new tenant alt_id if doesn't exists
     * @param App\Models\User $user
     * @param void
     */
    public function oneClickTenantAltId($data) {
        if ($data) {
            $alt = array_get($data, 'data.organization_alt_id');
            $class = get_class(auth()->user()->tenant);
            $altId = $this->alternativeIdRetrieve($alt, $class);
            if (!$altId) {
                $fields = ['alt_id' => $alt, 'label' => auth()->user()->tenant->subdomain, 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                $this->alternativeIdCreate(auth()->user()->tenant->id, $class, $fields);
            }
        }
    }

    /**
     * Stores Continue To Give API Key
     * @param Array $data
     * @return Void
     */
    public function oneClickSystemIntegration($data) {
        $integration = new Integration();
        array_set($integration, 'service', 'Continue to Give');
        array_set($integration, 'description', array_get($data, 'data.description'));
        if (auth()->user()->tenant->integrations()->save($integration)) {
            $value = new IntegrationValue();
            array_set($value, 'key', 'API_KEY');
            array_set($value, 'value', array_get($data, 'data.api_key'));
            array_set($value, 'tenant_id', auth()->user()->tenant->id);
            $integration->values()->save($value);
        }
    }

    /**
     * If user exists, then login
     * @param Array $param
     * @return void
     */
    public function getUserByHash($params) {
        $user = User::where('one_time_hash', array_get($params, 'one_time_hash'))->first();
        return $user;
    }

    public function generateToken($data) {
        //$tokenName = auth()->user()->tenant->subdomain . '.' . Constants::MAIN_DOMAIN;
        $tokenName = sprintf(env('APP_DOMAIN'), auth()->user()->tenant->subdomain);
        $tokenExists = auth()->user()->tenant->tokenExists($tokenName);

        $result = [];
        array_set($result, 'subdomain', auth()->user()->tenant->subdomain);
        array_set($result, 'system_name', env('APP_INTERNAL_NAME'));
        if (is_null($tokenExists)) {
            $this->oneClickSystemIntegration($data);
            $token = auth()->user()->createToken($tokenName);
            auth()->user()->tenant->setToken($token, $tokenName);
            array_set($result, 'requestor_system_api_key', $token->accessToken);
        } else {
            $token = $tokenExists;
            array_set($result, 'requestor_system_api_key', array_get($token, 'token'));
        }

        return $result;
    }

    public function oneClickLogin($user, $password) {
        if ($user) {
            if (Auth::attempt(['email' => array_get($user, 'email'), 'password' => $password, 'tenant_id' => array_get($user, 'tenant_id')])) {
                return true;
            }
        }
        return false;
    }

}
