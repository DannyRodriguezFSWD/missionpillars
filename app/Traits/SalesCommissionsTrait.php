<?php

namespace App\Traits;

use Carbon\Carbon;
use DB;

trait SalesCommissionsTrait 
{
    public function getSalesPerson()
    {
        return env('SALESPERSON');
    }
    
    public function createSalesCommission($tenant = null)
    {
        $tenantId = array_get($tenant, 'id');
        
        $commissions = DB::table('sales_commissions')->where('tenant_id', $tenantId)->get();
        
        if ($commissions->count() === 0) {
            DB::table('sales_commissions')->insertGetId([
                'tenant_id' => $tenantId,
                'tenant_name' => array_get($tenant, 'organization'),
                'salesperson' => $this->getSalesPerson(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
