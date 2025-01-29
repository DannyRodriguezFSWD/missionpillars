<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Constants;
use App\Models\Module;
use Carbon\Carbon;

/**
 * Description
 *
 * @author josemiguel
 */
class TenantObserver {
    
    public function created(Tenant $tenant) {
        $free = Module::withoutGlobalScopes()->where('id', 1)->first();
        if(!is_null($free)){
            array_set($tenant, 'free_month', true);
            //array_set($tenant, 'contact_fee', array_get($free, 'contact_fee'));
            array_set($tenant, 'start_billing_at', Carbon::now());
            array_set($tenant, 'next_billing_at', Carbon::now()->endOfMonth());
            array_set($tenant, 'last_billing_at', Carbon::now());
            $tenant->update();
        }
    }
    
}
