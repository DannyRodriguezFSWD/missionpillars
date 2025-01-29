<?php
namespace App\Classes\API;

use App\Classes\ContinueToGive\ContinueToGiveCampaigns;
use App\Classes\ContinueToGive\ContinueToGiveMissionaries;
use App\Classes\Transactions;
use App\Constants;

/**
 * Synchronizes data with Continue to give
 *
 * @author josemiguel
 */
class Sync {
    private function getToken() {
        $integration = auth()->user()->tenant->integrations()->where('service', 'Continue to Give')->first();
        $value = $integration->values()->where('key', 'API_KEY')->first();
        
        $token = array_get($value, 'value');
        return $token;
    }
    public function pages($request) {
        $token = $this->getToken();
        
        $campaigns = new ContinueToGiveCampaigns($token);
        $campaigns->run(['id' => array_get($request, 'alt_id')]);

        $missionaries = new ContinueToGiveMissionaries($token);
        $missionaries->run(['id' => array_get($request, 'alt_id')]);
    }
    
    public function transactions($request) {
        $params = ['id' => array_get($request, 'alt_id', 0)];
        $token = $this->getToken();
        $transactions = new Transactions($token);
        return $transactions->executeTransactions($params);
    }
    
    public function removeTenant($request)
    {
        $errors = [];
        
        $altId = array_get($request, 'alt_id', 0);
        
        if (empty($altId)) {
            $errors[] = 'MP: request has no alt_id';
        }
        
        $altIdObject = \App\Models\AltId::where([
            ['alt_id', '=', $altId],
            ['relation_type', '=', \App\Models\Tenant::class]
        ])->first();
        
        if (empty($altIdObject)) {
            $errors[] = 'MP: tenant not found';
        }
        
        $tenantId = array_get($altIdObject, 'relation_id', 0);
        
        if (!empty($tenantId)) {
            $tenant = \App\Models\Tenant::find($tenantId);
        }
        
        if (empty($tenant)) {
            $errors[] = 'MP: tenant not found';
        }
        
        if (!empty($errors)) {
            return ['errors' => $errors];
        }
        
        $removalType = array_get($request, 'removal_type', 0);
        
        if (!empty($tenant)) {
            if ($removalType === 'remove_account') {
                $tenant->delete();
            } elseif ($removalType === 'deactivate_modules') {
                $modules = $tenant->modules()->whereIn('module_id', [2, 3])->get();
                
                foreach ($modules as $module) {
                    $moduleId = array_get($module, 'id', 0);
                    
                    if ($moduleId === 2 || $moduleId === 3) {
                        $tenant->modules()->updateExistingPivot($moduleId, ['deleted_at' => date('Y-m-d H:i:s')]);
                    }
                }
                
                if (array_get($request, 'remove_invoices', 0) == 1) {
                    $tenant->unpaidInvoices()->delete();
                }
            } else {
                $errors[] = 'MP: bad removal type';
            }
        } else {
            $errors[] = 'MP: tenant not found';
        }
        
        return ['errors' => $errors];
    }
    
    public function getUnpaidInvoices($request)
    {
        $altId = array_get($request, 'alt_id', 0);
        
        if (empty($altId)) {
            return null;
        }
        
        $altIdObject = \App\Models\AltId::where([
            ['alt_id', '=', $altId],
            ['relation_type', '=', \App\Models\Tenant::class]
        ])->first();
        
        if (empty($altIdObject)) {
            return null;
        }
        
        $tenantId = array_get($altIdObject, 'relation_id', 0);
        
        if (empty($tenantId)) {
            return null;
        }
        
        $tenant = \App\Models\Tenant::find($tenantId);
        
        if (empty($tenant)) {
            return null;
        }
        
        $invoices = $tenant->unpaidInvoices()->get();
        
        if (empty($invoices)) {
            return null;
        } else {
            return $invoices;
        }
    }
}
