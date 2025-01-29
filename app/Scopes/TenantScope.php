<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Subdomains;

/**
 * Constraints all models to get data only if the current user or tenant id matches
 *
 * @author josemiguel
 */
class TenantScope implements Scope{
    use Subdomains;
    
    public function apply(Builder $builder, Model $model){
        $tenantid = null;
        
        if(auth()->check()){
            $tenantid = auth()->user()->tenant_id;
        }
        else{
            $url = \Illuminate\Support\Facades\Request::getHost();
            $subdomain = $this->getSubdomain($url);
            $tenant = $this->getTenant($subdomain);
            if(!$tenant){
                if(self::useTenantId()) $tenantid = self::useTenantId();
                else abort(404);
            } else {
                $tenantid = $tenant->id;
            }
        }
        
        $builder->where($model->getTable() . '.tenant_id', $tenantid)->orWhere($model->getTable() . '.tenant_id', null);
        return $builder;
    }


    /**
     * Sets a tenant to use for TenantScope if no auth
     * @param  [null|integer] $setvalue 
     * @return [null|integer]           
     */
    public static function useTenantId(int $setvalue = null) {
        static $currentvalue = null;
        if ($setvalue !== null) $currentvalue = $setvalue;
        return $currentvalue;
    }
}
