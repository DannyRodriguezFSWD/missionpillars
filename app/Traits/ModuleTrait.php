<?php

namespace App\Traits;

use App\Classes\MissionPillarsLog;
use App\Constants;
use App\Http\Controllers\PromocodesController;
use App\Models\Contact;
use App\Models\Module;
use App\Models\MPInvoice;
use App\Models\MPInvoiceDetail;
use App\Models\PaymentOption;
use App\Models\Promocode;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;

use Carbon\Carbon;
use DB;
use Stripe\Stripe;

/**
 *
 * @author josemiguel
 */
trait ModuleTrait {
    use SendsCustomerServiceEmails;
    use \App\Traits\UpdatesPaymentOptions;
    use SalesCommissionsTrait;
    
    public function can($feature){
        $can = $this->modules()->whereHas('features', function($query) use($feature){
            $query->where('name', $feature);
        })->count();
        return $can > 0;
    }

    /**
     * NOTE moved from SubscriptionsController
     */
    public function enableModule($id, $request, $tenant = null)
    {
        $module = Module::findOrFail($id);
        
        if (is_null($tenant)) {
            $tenant = auth()->user()->tenant;
        }
        
        $now = Carbon::now();
        $start_billing_at = Carbon::now();
        $next_billing_at = Carbon::now();
        $last_billing_at = Carbon::now();
        $app_fee = array_get($module, 'app_fee', 0);
        $sms_fee = array_get($module, 'sms_fee', 0);
        $email_fee = array_get($module, 'email_fee', 0);
        $contact_fee = array_get($module, 'contact_fee', 0);
        $code = null;
        $isTrial = 0;

        //if they added a paid module for first time
        if(array_get($module, 'id') == 2 && (array_get($tenant, 'free_month') == 1 || is_null(array_get($tenant, 'free_month')))){
            $start_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS);
            $next_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS)->endOfMonth();
            $last_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS);
            $isTrial = 1;
            array_set($tenant, 'free_month', 0);
            $tenant->update();
        }

        if(array_get($module, 'id') == 3 && (array_get($tenant, 'accounting_free_month') == 1 || is_null(array_get($tenant, 'accounting_free_month')))){
            $start_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS);
            $next_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS)->endOfMonth();
            $last_billing_at = Carbon::now()->addDays(Constants::MODULE_FREE_DAYS);
            array_set($tenant, 'accounting_free_month', 0);
            $tenant->update();
        }

        if ($request->input('promo_code') && $request->input('promo_code') !== '') {
            $pcc = new PromocodesController();
            if ($pcc->checkPromoCode($request->input('promo_code'))) {
                $promoCode = Promocode::code($request->input('promo_code'))->first();
                $code = $request->input('promo_code');
                if ($promoCode->quantity > 0) {
                    $discount = $promoCode->reward;
                    $promoCode->quantity --;
                    $promoCode->save();
                } else if ($promoCode->quantity < 0) {
                    $discount = $promoCode->reward;
                }
            }
        }

        // Only update Salesmate if they are no on trial
        if ($isTrial == 0) {
            $salesmate = new \App\Classes\Salesmate\Salesmate();
            $salesmate->updateDealToWon($tenant, $module);
        }
        
        // Activating module
        // NOTE this sync essentially adds a new or updates an existing row matching the module id. e.g., if a soft-deleted row matches, it will 'restore' that row (see deleted_at value below)
        $tenant->modules()->sync([
            $id => [
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'start_billing_at' => $start_billing_at,
                'next_billing_at' => $next_billing_at->endOfMonth(),
                'last_billing_at' => $last_billing_at,
                'app_fee' => $app_fee,
                'sms_fee' => $sms_fee,
                'email_fee' => $email_fee,
                'contact_fee' => $contact_fee,
                'promo_code' => $code,
                'discount_amount' => $discount ?? null,
                'is_trial' => $isTrial,
                'reactivate_on_paid_invoice_id' => null
            ]
        ], false);
    }
    
    /**
     * This is called by RegisterController, OneClickController, and TenantsController::update
     */
    public function upgrade($request){
        //we only add free module on first signup, then we not add it again when upgrade
        $free = $this->modules()->where('id', 1)->first();
        if(is_null($free)){
            $free = Module::findOrFail(1);
            if(!is_null($free)){
                $now = Carbon::now();
                $this->modules()->sync([
                    array_get($free, 'id', 1) => ['created_at' => $now, 'updated_at' => $now]
                ], false);
            }
        }
        
        // TODO Fix this code to support adding paid modules by updating the appropriate tenant_module rows (not the current tenant object)
        /*
        $chms = null;
        if($request->has('chms')){
            $chms = Module::findOrFail(2);
        }

        $accounting = null;
        if($request->has('accounting')){
            $accounting = Module::findOrFail(3);
        }

        if(!is_null($chms)){
            array_set($this, 'chms_app_fee', array_get($chms, 'app_fee', 50));
            //array_set($this, 'phone_number_fee', array_get($chms, 'phone_number_fee', 10));
            array_set($this, 'sms_fee', array_get($chms, 'sms_fee', 0.02));
            array_set($this, 'email_fee', array_get($chms, 'email_fee', 0.02));
            $this->update();
        }

        if(!is_null($accounting)){
            array_set($this, 'accounting_app_fee', array_get($accounting, 'app_fee', 25));
            $this->update();
        }

        if(!is_null($chms) || !is_null($accounting)){
            array_set($this, 'start_billing_at', \Carbon\Carbon::now()->addMonth());
            array_set($this, 'next_billing_at', \Carbon\Carbon::now()->addMonth()->endOfMonth());
            $this->update();
        }
         */
    }

    /**
     * start date of either last_billing_at or start_billing_at whichever is greater
     */
    public function getBillingStartDate($module)
    {
        return $module->pivot->last_billing_at > $module->pivot->start_billing_at 
        ? $module->pivot->last_billing_at : $module->pivot->start_billing_at;
    }
    
    /**
     *  When modules are billed, the number of days to bill is calculated 
     * if the days is >= 30 then the days will be 30 (doesn't handle february, and the non-edge-case of billing, e.g., 35 days, 60 days, etc)
     */
    public function getNumberOfDaysToBill($module, $bill_from, $billingDay = null)
    {
        if (!$billingDay) {
            $billingDay = Carbon::now();
        }
        
        $today = $billingDay->endOfDay();
        $enddate = $today;
        
        if ($module->pivot && $module->pivot->cancel && $module->pivot->cancelation_requested_at) {
            $enddate = Carbon::parse($module->pivot->cancelation_requested_at);
        }

        //if they have 1 month free, then $bill_from will be in future
        // so we wont charge app fee
        if($enddate->lt($bill_from)){
            $days_to_bill = 0;
        }
        else{
            $days_to_bill = $enddate->copy()->diffInDays($bill_from);
        }

        if($days_to_bill >= 30){
            $days_to_bill = Constants::SUBSCRIPTION_DAYS_IN_MONTH;
        }
        
        return $days_to_bill;
    }
    
    
    public function billModule($module, $tenant, $billingDay = null){
        if (!$billingDay) {
            $billingDay = Carbon::now();
        }
        
        $today = $billingDay->endOfDay();
        $bill_from = $this->getBillingStartDate($module);
        
        $days_to_bill = $this->getNumberOfDaysToBill($module, $bill_from, $billingDay);

        
        $discount_percent = $module->pivot->discount_amount;

        // NOTE this if/else block changes $discount_percent from the percent discounted to the percent billed 
        if ($discount_percent && $discount_percent !== '' && $discount_percent <= 1) {
            $billed_percent = 1 - $discount_percent;
        } else {
            $billed_percent = 1;
            $discount_percent = 0;
        }

        // various usage fees
        $phone_number_fee = 0+array_get($module, 'pivot.phone_number_fee', array_get($module, 'phone_number_fee', 0));
        $sms_fee = 0+array_get($module, 'pivot.sms_fee', array_get($module, 'sms_fee', 0));
        $email_fee = 0+array_get($module, 'pivot.email_fee', array_get($module, 'email_fee', 0));
        $contact_fee = 0+array_get($module, 'pivot.contact_fee', array_get($module, 'contact_fee', 0));
        
        // Calculate (prorated, if necessary) monthly fee
        $monthly_amount = 0+array_get($module, 'pivot.app_fee', array_get($module, 'app_fee', 0));
        $app_fee = $monthly_amount * ($days_to_bill / Constants::SUBSCRIPTION_DAYS_IN_MONTH);
        
        // calculate billed usage fees based on usage
        if($sms_fee > 0){
            $sent_sms = $tenant->SMSSent()->noTenantScope()->where('status', '!=', 'Queued')
                ->whereBetween('created_at', [
                    $today->copy()->startOfMonth(), $today->copy()->endOfMonth()
                ])->count();
            $sms_fee = $sms_fee * $sent_sms;
        }
        
        if($email_fee > 0){
            $sent_emails = $tenant->emailsSent()->noTenantScope()->where('sent', true)
                ->whereBetween('created_at', [
                    $today->copy()->startOfMonth(), $today->copy()->endOfMonth()
                ])->count();
            $email_fee = $email_fee * $sent_emails;
        }

        if($contact_fee > 0 && $bill_from <= $today){
            $contacts = $tenant->contacts()->noTenantScope()->count();
            $contact_fee = $contact_fee * $contacts;
        }
        else{//free month
            $contact_fee = 0;
        }

        
        // total everything and return
        $total_amount = $billed_percent * ($app_fee + $phone_number_fee + $sms_fee + $email_fee + $contact_fee);
        $discount = -1 * $discount_percent * ($app_fee + $phone_number_fee + $sms_fee + $email_fee + $contact_fee);
        $result = [
            'module_id' => array_get($module, 'id'),
            'cancel_module' => array_get($module, 'pivot.cancel'),
            'module_name' => array_get($module, 'name'),
            'billing_from' => $bill_from,
            'billing_to' => $today,
            'total_amount' => $total_amount,
            'payment_option_id' => array_get($tenant, 'payment_option_id'),
            'details' => [
                'app_fee' => $app_fee,
                'phone_number_fee' => $phone_number_fee,
                'sms_fee' => $sms_fee,
                'email_fee' => $email_fee,
                'contact_fee' => $contact_fee,
                'discount' => $discount, 
            ]
        ];

        return $result;
        
    }

    /**
     * creates invoice
     * @param array $records
     * @param boolean $all_in_one (indicates if invoice will contain many modules)
     * @param date $billingDay use this if you want to set the day of billing to a specific date (default is today)
     */
     public function createInvoice($records, $all_in_one = false, $billingDay = null){
         if(!$all_in_one) return false; // NOT IMPLEMENTED
         
         $modules = array_pluck($records, 'module_name');
         $module_name = implode(' + ', $modules);
         $amounts = array_pluck($records, 'total_amount');
         $total_amount = array_sum($amounts);
         
         $sub = str_pad($this->invoices()->noTenantScope()->count()+1, 6, "0", STR_PAD_LEFT);
         $reference = implode('-', [array_get($this, 'id'), $sub]);
         $min_billing_from = min(array_pluck($records, 'billing_from'));
         $max_billing_to = max(array_pluck($records, 'billing_to'));
         $invoice = DB::table('mp_invoices')->insertGetId([
             'tenant_id' => array_get($this, 'id'),
             'reference' => $reference,
             'module_name' => $module_name,
             'billing_from' => $min_billing_from,
             'billing_to' => $max_billing_to,
             'total_amount' => $total_amount,
             'payment_option_id' => array_get($this, 'payment_option_id'),
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
             'paid_at' => $total_amount ? null: Carbon::now(), // mark 0 balance invoices as paid
         ]);
         
         $this->createInvoiceDetails($invoice, $records, $all_in_one);
         if($total_amount > 0){ //charge
             $this->chargeInvoice($invoice);
         }
         
         //finally update billing dates
         if (!$billingDay) {
             $billingDay = Carbon::now();
         }
         
         $today = $billingDay;
         $next_billing_at = Carbon::parse($max_billing_to);
         
         // loop through bill records and make tenant_modules changes
         foreach($records as $record){
             $date = $next_billing_at->copy();
             $module_id = array_get($record, 'module_id');
             $this->modules()->updateExistingPivot($module_id, [
                 'next_billing_at' => $date->addDays(7)->endOfMonth(),
                 'last_billing_at' => $today,
             ]);
             $date = null;
             
             //here we need to update all fees if they canceled modules
             if(array_get($record, 'cancel_module') == 1){
                 $this->modules()->updateExistingPivot($module_id, [
                     'deleted_at' => $today,
                     'reactivate_on_paid_invoice_id' => null,
                 ]);
             }
         }
         
         return true;
     }

    public function createInvoiceDetails($invoice, $record, $all_in_one = false){
        
        if($all_in_one){
            foreach($record as $item){
                foreach (array_get($item, 'details', []) as $key => $value) {
                    if($value != 0){
                        $detail = DB::table('mp_invoice_details')->insertGetId([
                            'tenant_id' => array_get($this, 'id'),
                            'mp_invoice_id' => $invoice,
                            'description' => array_get($item, 'module_name').': '.Constants::SUBSCRIPTION_INVOICE_DETAILS[$key],
                            'amount' => $value,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
            
        }

    }

    public function updatePhoneNumberFee(){
        $num_phone_numbers = $this->SMSPhoneNumbers()->noTenantScope()->count();

        //we are adding because this will work when we allow people to buy more than 1 phone number
        $phone_number_fee = $num_phone_numbers * env('APP_SMS_MONTHLY_FEE', 10);

        if($phone_number_fee < 0){//we wont allow negative fees
            $phone_number_fee = 0;
        }

        $this->modules()->updateExistingPivot(2, ['phone_number_fee' => $phone_number_fee]);
        
    }

    /**
     * NOTE because this currently depends on payment_option_id, this method should be called on a Tenant object
     */
    public function chargeInvoice($invoice_id){
        $isChargeSuccess = false;
        try{
           $customer =  $stripe_total_amount = $charge = null;
            $invoice = MPInvoice::noTenantScope()->where('id', $invoice_id)->first();
            if(is_null($invoice)){
                // handle error
                TenantScope::useTenantId(false);
                return null;
            }
            
            $modulename = $this->getModuleNameFromInvoice($invoice);
            
            $total_amount = array_get($invoice, 'total_amount', 0) + 0;//add 0 to force number
            $payment_option = PaymentOption::noTenantScope()
                ->find(array_get($this, 'payment_option_id'));
            
            if(is_null($payment_option)) {
                throw new \Exception("No payment options exist");
                return false;
            }

            $contact = Contact::noTenantScope()->withTrashed()
                ->where('id',array_get($payment_option, 'contact_id'))->first();
            if(is_null($contact)) {
                throw new \Exception("No primary contact exists");
                return false;
            }
            
            $customer = User::noUserScope()->withTrashed()
            ->where('id', array_get($contact, 'user_id'))->first();
            if(is_null($customer)) {
                throw new \Exception("No primary user exists");
                return false;
            } elseif (!$customer->stripe_id){
                throw new \Exception("No stripe customer exists for user ({$customer->id})");
                return false;
            }

            // process stripe payment
            $stripe_total_amount = $total_amount * 100;//stripe requires cents
            $charge = $customer->charge($stripe_total_amount); //this will throw a throwable if not successful
            $isChargeSuccess = $charge->paid;
            DB::table('mp_invoices')->where('id', $invoice_id)
            ->update([
                'paid_at' => Carbon::now(),
                'payment_id' => array_get($charge, 'id'),
                'message' => array_get($charge, 'outcome.seller_message')
            ]);

            $this->logInvoiceCharge($invoice, $customer, $stripe_total_amount, $charge);
            $this->createSalesCommission($contact->tenant);
        }
        catch(\Throwable $th) {
            if (!$isChargeSuccess) $this->logInvoiceCharge($invoice, $customer, $stripe_total_amount, null, $th, $modulename);
        }
        catch(\Exception $e){
            if (!$isChargeSuccess) $this->logInvoiceCharge($invoice, $customer, $stripe_total_amount, null, $e, $modulename);
        }

        TenantScope::useTenantId(false);
        return $isChargeSuccess;
    }
    
    /**
     * Logs a stripe invoice charge.
     * @param  [MPInvoice] $invoice             
     * @param  [User] $customer            
     * @param  [integer] $stripe_total_amount 
     * @param  [object] $charge             A charge as returned by Stripe 
     * @param  [\Exception|\Throwable] $e                   Optional. If provided also logs failure information
     */
    public function logInvoiceCharge($invoice, $customer, $stripe_total_amount, $charge, $e = null, $modulename = null)
    {
        MissionPillarsLog::log([
            'event' => 'invoices',
            'url' => Stripe::$apiBase,
            'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
            'request' => json_encode([
                'amount' => $stripe_total_amount,
                'user_id' => array_get($customer, 'id'),
                'tenant_id' => array_get($customer, 'tenant_id')
            ]),
            'response' => json_encode($charge),
            'code' => $e ? $e->getCode() : null,
            'message' => $e ? $e->getMessage() : null,
        ]);
        
        if($e) {
            $user = $customer;
            $tenant = $invoice->tenant;
            if (!$user) {
                $user= $tenant->users()->first();
            }
            $now = Carbon::now();
            TenantScope::useTenantId($invoice->tenant_id); 
            // notify customer service
            $subject = 'Failure to bill MissionPillars customer';
            $email_message = (is_a($e, '\Stripe\Error\Base')
            ? "Stripe returned an error" : "An issue was encountered")
            ." while attempting to bill customer ({$tenant->id})<p>Message: {$e->getMessage()}" ;
            $this->sendCustomerServiceEmail($subject, $email_message, compact('user','tenant')); 
            
            // remove stripe payment options
            if ($user) {
                $this->removeStripePaymentOptions($user);
            }
            
            // soft delete the tenant's module subscriptions (until the they update their billing)
            $tenant_modules = $tenant->modulesWithFee;
            foreach ($tenant_modules as $tenant_module) {
                $tenant->modules()->updateExistingPivot($tenant_module->id, [
                    'deleted_at' => $now,
                    'reactivate_on_paid_invoice_id' => $invoice->id,
                ]);
            }
            
            // record 'failed' in the billing/invoice data
            $invoice->message = "Payment failed. ($now)";
            $invoice->save();
            
            $salesmate = new \App\Classes\Salesmate\Salesmate();
            $salesmate->createBillingFailedActivity($invoice->tenant, "Billing failed: ".$e->getMessage(), $modulename);
            
            TenantScope::useTenantId(false); // clear
        }
        
    }

    /**
     * @param  [MPInvoice] $invoice 
     * @return [Collection]          A collection modules represented by the description of the invoice details
     */
    public function getModulesFromInvoice($invoice)
    {
        $modules = collect();
        if (!$invoice) return $modules;
        
        $descriptions = $invoice->details()->noTenantScope()->pluck('description');
        foreach ($descriptions as $description) {
            if ($description == 'Church/Donor/Marketing management: Module fee') $modules[] = Module::find(2);
            if ($description == 'Accounting: Module fee') $modules[] = Module::find(3);
        }
        
        return $modules;
    }
    
    
    /**
     * Pay unpaid invoices and reactivate related modules
     * @param  [Tenant] $tenant 
     * @return [boolean]         true if charge (and thus reactivation) is successful, false otherwise
     */
    public function payUnpaidInvoices($tenant)
    {
        foreach ($tenant->unpaidInvoices as $invoice) {
            if (!$tenant->chargeInvoice($invoice->id)) return false;
        }
        
        return true;
    }
    
    /**
     * Checks the invoice's details and returns which module it is for, it is both.
     * @param  MPInvoice $invoice 
     * @return null|string      "CRM" or "Accounting" if only one module, null if both 
     */
    public function getModuleNameFromInvoice($invoice) {
        if ($invoice->details()->noTenantScope()->forCRM() 
        && $invoice->details()->noTenantScope()->forAccounting()->count()) return null;
        return $invoice->details()->noTenantScope()->forCRM() ? "CRM" : "Accounting";
    }
}
