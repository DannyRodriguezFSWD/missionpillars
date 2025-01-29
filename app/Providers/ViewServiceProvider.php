<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (!auth()->check()) {
                $view->with('trialModule', null);
                $view->with('paymentOption', null);
            } else {
                if (!auth()->user()->can('settings-view')) {
                    $view->with('trialModule', null);
                    $view->with('paymentOption', null);
                } else {
                    $trialModule = auth()->user()->tenant->modulesTrial->first();
                    $paymentOption = auth()->user()->tenant->paymentOptions()->stripe()->first();
                    $view->with('trialModule', $trialModule);
                    $view->with('paymentOption', $paymentOption);

                    if ($trialModule && !$paymentOption) {
                        $promocodes = $this->getAutoAppliedPromocodes();
                        $discounts = $this->getDiscounts($promocodes);
                        $amount_unpaid = \App\Models\MPInvoice::unpaid()->sum('total_amount');

                        $view->with('chms', \App\Models\Module::find(2));    
                        $view->with('acct', \App\Models\Module::find(3));    
                        $view->with('promocodes', $promocodes);    
                        $view->with('discounts', $discounts);    
                        $view->with('amount_unpaid', $amount_unpaid);
                    }
                }
            }
        });
    }
    
    protected function getAutoAppliedPromocodes()
    {
        // none by default
        $promocodes = [
            'CRM' => null,
            'Accounting' => null,
        ];
        
        // get missionary codes
        if (auth()->user()->tenant->type == 'missionary') {
            $promocodes['CRM'] = \App\Models\Promocode::where('code','missionarycrm2020')
            ->valid()->pluck('code')->first();
            
            $promocodes['Accounting'] = \App\Models\Promocode::where('code','missionaryacc2020') ->valid()->pluck('code')->first();
        }
        
        return $promocodes;
    }
    
    
    protected function getDiscounts($promocodes)
    {
        // note this is purposely designed to work with getAutoAppliedPromocodes
        // none by default
        $discounts = [
            'CRM' => null,
            'Accounting' => null,
            'withpromocode' => [
                'CRM' => false,
                'Accounting' => false,
            ],
            'currentmodulefee' => [
                'CRM' => false,
                'Accounting' => false,
            ],
            'currentcrmcontactfee' => false
        ];
        
        // get missionary codes
        if (auth()->user()->tenant->type == 'missionary') {
            foreach (['CRM','Accounting'] as $module) {
                $discounts[$module] = \App\Models\Promocode::where('code',$promocodes[$module])
                ->valid()->pluck('reward')->first();
                $discounts['withpromocode'][$module] = !is_null($discounts[$module]);
            }
        }
        
        foreach ([2=>'CRM',3=>'Accounting'] as $id =>$module) {
            $tenantmodule = auth()->user()->tenant->modules()->where('module_id',$id)->first();
            if ($tenantmodule) {
                $discounts[$module] = $tenantmodule->pivot->discount_amount;
                $discounts['withpromocode'][$module] = false;
                $discounts['currentmodulefee'][$module] = $tenantmodule->pivot->app_fee;
                if ($module == 'CRM') $discounts['currentcrmcontactfee'] = $tenantmodule->pivot->contact_fee;
            }
        }
        
        return $discounts;
    }
}
