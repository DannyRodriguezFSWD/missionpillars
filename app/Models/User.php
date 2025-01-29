<?php

namespace App\Models;

use App\Classes\Subdomains\TenantSubdomain;
use App\Constants;
use App\Models\Tenant;
use App\MPLog;
use App\Notifications\CustomResetPassword;
use App\Observers\UserObserver;
use App\Scopes\UserScope;
use App\Traits\Users\ContactTrait;
use App\Traits\Users\UserLoginTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Request;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable {

    use HasApiTokens, Notifiable, ContactTrait, UserLoginTrait, Billable;
    use EntrustUserTrait, Authorizable {
        EntrustUserTrait::can insteadof Authorizable;
        Authorizable::can as canDo;
        restore as private restoreEntrustUserTrait;
    }
    use SoftDeletes {
        restore as private restoreSoftDeletes;
    }

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'c2g_id', 'last_name', 'tenant_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function boot() {
        parent::boot();
        User::observe(new UserObserver());
        static::addGlobalScope(new UserScope);
    }

    
    /** Overrides **/
    
    /**
     * Determine if the entity does not have a given ability.
     * Overrides namespace Illuminate\Foundation\Auth\Access\Authorizable::cant and, by extension, ::cannot
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function cant($ability, $arguments = [])
    {
        return ! $this->canDo($ability, $arguments);
    }


    /** Scopes **/

    public function scopeNoUserScope($query) {
        return $query->withoutGlobalScope(UserScope::class);
    }

    /**
     * TODO consider making other role-based scopes
     */
    public function scopeOrganizationOwner($query) {
        return $query->whereHas('roles', function($q) { $q->where('id', 1); });
    }

    public function scopeStripeCustomer($query) {
        return $query->whereNotNull('stripe_id');
    }
    
    public function scopeSuperAdmin($query) {
        return $query->where('is_super_admin',1);
    }



    /** Relationships **/

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function contact() {
        return $this->hasOne(Contact::class);
    }

    public function restore() {
        $this->EntrustUserTrait();
        $this->restoreSoftDeletes();
    }

    public function altId() {
        return $this->hasOne(AltId::class, 'relation_id', 'id')->where('relation_type', User::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $url = Request::getHost();
        $app = sprintf(env('APP_DOMAIN'), 'app');
        $tenant = null;

        if(strpos($app, $url) === false){//not main subdomain, we need to check custom subdomain
            $request = request();
            $tenant = TenantSubdomain::getTenant($request);
            if(!$tenant){//not a registered tenant
                abort(401);
            }
        }

        //$link = sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain'));
        $link = sprintf(env('APP_DOMAIN'), 'app');
        try {
            $this->notify(new CustomResetPassword($token, $link, $tenant));
        } catch (\Throwable $th) {
            MPLog::create([
                'event' => 'mailgun',
                'caller_function' => __FUNCTION__,
                'code' => $th->getCode(),
                'message' => $th->getMessage(),
                'data' => json_encode(['token' => $token, 'link' => $link, 'tenant' => $tenant])
            ]);
        }

    }

    public function isOrganizationOwner(){
        return $this->roles[0]->name == 'organization-owner';
    }

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->last_name;
    }
}
