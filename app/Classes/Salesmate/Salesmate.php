<?php

namespace App\Classes\Salesmate;

class Salesmate extends SalesmateAPI
{
    public function missionPillarsSignup($data) 
    {
        $contact = $this->findOrCreateContact($data);
        array_set($data, config('salesmate.customFields.companies.statusCrm'), 'Created Organization');
        $company = $this->findOrCreateCompany($data, $contact);
        $this->setContactInCompany($contact, $company);
        $stage = config('salesmate.pipelines.mp.stages.organizationCreated');
        $crmDeal = $this->findOrCreateDeal(auth()->user()->id, $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.crm'));
        $this->updateDeal($crmDeal, ['stage' => $stage]);
        $acctDeal = $this->findOrCreateDeal(auth()->user()->id, $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.acct'));
        $this->updateDeal($acctDeal, ['stage' => $stage]);
    }
    
    public function updateDealToWon($tenant, $module)
    {
        $user = $tenant->users()->organizationOwner()->first();
        
        $contact = $this->findOrCreateContact($user);
        $company = $this->findOrCreateCompany($user, $contact);
        $this->setContactInCompany($contact, $company);
        $stage = config('salesmate.pipelines.mp.stages.activated');
        
        if (array_get($module, 'id') === 2) {
            $crmDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.crm'));
            $this->updateDeal($crmDeal, [
                'status' => 'Won',
                'stage' => $stage
            ]);
        } elseif (array_get($module, 'id') === 3) {
            $acctDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.acct'));
            $this->updateDeal($acctDeal, [
                'status' => 'Won',
                'stage' => $stage
            ]);
        }
    }

    public function createBillingFailedActivity($tenant, $message, $modulename) 
    {
        $user = $tenant->users()->withoutGlobalScopes()->whereNull('deleted_at')->organizationOwner()->first();

        if (empty($user)) {
            return false;
        }
        
        $contact = $this->findOrCreateContact($user);
        $company = $this->findOrCreateCompany($user, $contact);
        
        if (empty($contact) || emptY($company)) {
            return false;
        }
        
        $this->setContactInCompany($contact, $company);
        $stage = config('salesmate.pipelines.mp.stages.failedBilling');
        
        if ($modulename === 'CRM' || is_null($modulename)) {
            $crmDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.crm'));
            $this->updateDeal($crmDeal, [
                'status' => 'Open',
                'stage' => $stage
            ]);
        } 
        
        if ($modulename === 'Accounting' || is_null($modulename)) {
            $acctDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.acct'));
            $this->updateDeal($acctDeal, [
                'status' => 'Open',
                'stage' => $stage
            ]);
        }

        $this->createActivityForContact($contact, "$modulename: $message");
    }
    
    public function updateToTrialEnded($tenant, $module)
    {
        if (empty($tenant) || empty($module)) {
            return false;
        }
        
        $user = $tenant->users()->withoutGlobalScopes()->whereNull('deleted_at')->organizationOwner()->first();
        
        if (empty($user)) {
            return false;
        }
        
        $moduleId = array_get($module, 'id');
        
        if ($moduleId != 2 && $moduleId != 3) {
            return false;
        }
        
        $contact = $this->findOrCreateContact($user);
        $company = $this->findOrCreateCompany($user, $contact);
        
        if (empty($contact) || emptY($company)) {
            return false;
        }
        
        $this->setContactInCompany($contact, $company);
        $stage = config('salesmate.pipelines.mp.stages.trialEnded');
        
        if ($moduleId == 2) {
            $crmDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.crm'));
            $this->updateDeal($crmDeal, [
                'status' => 'Open',
                'stage' => $stage
            ]);
            $this->updateCompany($company, [config('salesmate.customFields.companies.statusCrm') => 'Trial Ended']);
        } else if ($moduleId == 3) {
            $acctDeal = $this->findOrCreateDeal(array_get($user, 'id'), $contact, $stage, config('salesmate.pipelines.mp.name'), 'Open', config('salesmate.pipelines.mp.titles.acct'));
            $this->updateDeal($acctDeal, [
                'status' => 'Open',
                'stage' => $stage
            ]);
            $this->updateCompany($company, [config('salesmate.customFields.companies.statusAcct') => 'Trial Ended']);
        }
    }
}
