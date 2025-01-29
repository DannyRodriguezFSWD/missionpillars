<?php
namespace App\Classes\Commands\Billing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Module;
use App\Models\SMSSent;
use App\Models\EmailSent;
use App\Classes\Email\EmailQueue;
use App\Models\MPInvoice;
use App\Classes\MissionPillarsLog;
use App\Models\Contact;
use App\Models\User;

use App\Scopes\TenantScope;

/**
 * use $billingDay to set up which day the billing is run (by default it's today)
 */
class Billing {

    public function run($billingDay = null){
        // if( $this->isEndOfMonth() || env('APP_MODE') == 'developer') {
            $this->billTenants($billingDay);
        // }
    }

    public function isEndOfMonth(){
        $today = Carbon::now()->endOfDay();
        $endofmonth = Carbon::now()->endOfMonth();
        
        /*
        if(env('APP_MODE') == 'developer'){//we allow to run billind anytime
            return true;
        }
        */
        return $today->toDateString() == $endofmonth->toDateString();
    }

    /**
     * @return [array] Id values of tenants invoiced today
     */
    public function getInvoicedTenantIds($billingDay = null) 
    {
        if (!$billingDay) {
            $billingDay = Carbon::now();
        }
        
        $start = $billingDay->copy()->startOfDay();
        $end = $billingDay->copy()->endOfDay();
        $tenant_ids = MPInvoice::noTenantScope()
        ->whereBetween('billing_to', [ $start, $end ] )
        ->pluck('tenant_id')->toArray();
        
        return $tenant_ids;
    }
    
    /**
     * The function determines the dates used when filtering by next_billing_at
     * between a month ago and the end of the day 
     */
    public function getBillingRange($billingDay = null) 
    {
        if (!$billingDay) {
            $billingDay = Carbon::now();
        }
        
        $enddate = $billingDay->endOfDay();
        $startdate = $enddate->copy()->startOfDay()->subMonth();
        
        return array($startdate,$enddate);
    }
    
    /**
     * Uses getBillingRange to get tenants that can currently be invoiced ignoring those already invoiced today
     */
    public function getTenantsToBill($billingDay = null)
    {
        // Avoid re-invoicing tenants that were already invoiced
        $invoiced_tenant_ids = $this->getInvoicedTenantIds($billingDay);
        
        $betweendates = $this->getBillingRange($billingDay);
        return Tenant::whereHas('modules', function($q) use($betweendates){
            $q->whereBetween('next_billing_at', $betweendates);
            $q->whereRaw('(tenant_modules.app_fee+tenant_modules.phone_number_fee+tenant_modules.sms_fee+tenant_modules.email_fee+tenant_modules.contact_fee) > 0');
        })->whereNotIn('id', $invoiced_tenant_ids)->get();
    }
    
    /**
     * Gets a primary contact from the tenant, assuming that the first user created for the tenant is the primary contact
     * TODO consider defining primary contact explictly
     * @param  [Tenant] $tenant 
     * @return [Contact]       
     */
    public function getPrimaryContact($tenant)
    {
        TenantScope::useTenantId($tenant->id);
        $user = $tenant->users()->first();
        $contact = $user->contact;
        if (!$contact) {
            $user->createContact();
            $contact = $user->contact;
        }
        
        return $contact;
    }
    
    /**
     * Sends prepared invoices to the contact
     * @param  [Tenant] $tenant  
     * @param  [Contact] $contact 
     */
    public function sendInvoiceToContact($tenant, $contact)
    {
        $view = view('emails.send.invoice')->with([
            'tenant' => $tenant,
            'contact' => $contact
        ]);
        
        $args = [
            'subject' => 'Mission Pillars - Your subscription is renewed',
            'content' => $view->render(),
            'model' => $contact->user
        ];
        
        EmailQueue::set($contact, $args);
    }
    
    /**
     * Creates and, if possible, sends invoices to billable tenants
     */
    protected function billTenants($billingDay = null){
        $tenants = $this->getTenantsToBill($billingDay);
        foreach ($tenants as $tenant) {
            try{
                $bill = [];
                foreach(array_get($tenant, 'modules') as $module){
                    $module_billed = $tenant->billModule($module, $tenant, $billingDay);
                    array_push($bill, $module_billed);
                }
                if(count($bill) > 0){
                    $tenant->createInvoice($bill, true, $billingDay);
                    $contact = $this->getPrimaryContact($tenant);
                    
                    if(!is_null($contact)){
                        $this->sendInvoiceToContact($tenant, $contact);
                    }
                }
            }
            catch (\Throwable $th) {
                MissionPillarsLog::log([
                    'event' => 'invoices',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'code' => $th->getCode(),
                    'message' => $th->getMessage(),
                    'data' => $th->getTraceAsString()
                ]);
            }
            catch(\Exception $e){
                MissionPillarsLog::log([
                    'event' => 'invoices',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'data' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function checkTrial()
    {
        $tenants = $this->getTenantsOnTrial();
        
        if (!empty($tenants)) {
            foreach ($tenants as $tenant) {
                $modules = $tenant->modules()->where('id', '<>', 1)->where('is_trial', 1)->get();
                
                if (!empty($modules)) {
                    foreach ($modules as $module) {
                        $this->checkTrialForModule($tenant, $module);
                    }
                }
            }
        }
    }
    
    public function getTenantsOnTrial()
    {
        $tenants = Tenant::with('modules')->whereHas('modules', function ($query) {
            $query->where('is_trial', 1);
        })->get();
                
        return $tenants;
    }
    
    public function checkTrialForModule($tenant, $module)
    {
        $timeTrialEnd = array_get($module, 'pivot.start_billing_at');
        $timeNow = Carbon::now()->format('Y-m-d H:i:s');
        
        if (strtotime($timeNow) > strtotime($timeTrialEnd)) {
            $tenant->modules()->updateExistingPivot(array_get($module, 'id'), [
                'deleted_at' => $timeNow,
                'is_trial' => 0
            ]);
            
            $salesmate = new \App\Classes\Salesmate\Salesmate();
            $salesmate->updateToTrialEnded($tenant, $module);
        }
    }
}
