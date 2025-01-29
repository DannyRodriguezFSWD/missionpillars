<?php

namespace App\Observers\Pledges;

use App\Constants;
use App\Models\TransactionTemplate;
use App\Models\TransactionTemplateSplit;
use App\Classes\Email\Mailgun\PledgeEmailNotification;
use App\Models\User;
use App\Models\Contact;
use App\Models\Purpose;
use App\Models\Campaign;
use App\Models\Tenant;

/**
 * Description of ChatOfAccountObserver
 *
 * @author josemiguel
 */
class PledgeObserver {
    
    public function created(TransactionTemplateSplit $split) {
        $pledge = TransactionTemplateSplit::withoutGlobalScopes()
                ->whereHas('template', function($query){
                    $query->where('is_pledge', true);
                })
                ->where('id', array_get($split, 'id'))->first();        
        $tenant = Tenant::withoutGlobalScopes()->where('id', array_get($split, 'tenant_id'))->first();
                
        if(!is_null($pledge)){
            $template = TransactionTemplate::withoutGlobalScopes()->where('id', array_get($pledge, 'transaction_template_id'))->first();
            $user = User::withoutGlobalScopes()->where([['tenant_id', '=', array_get($pledge, 'tenant_id')]])->first();
            $contact = Contact::withoutGlobalScopes()->where('id', array_get($template, 'contact_id'))->first();
            $chart = Purpose::withoutGlobalScopes()->where('id', array_get($pledge, 'purpose_id'))->first();
            $campaign = Campaign::withoutGlobalScopes()->where('id', array_get($pledge, 'campaign_id'))->first();
            
            $args = [
                'user' => $user,
                'contact' => $contact,
                'chart' => $chart,
                'campaign' => $campaign,
                'split' => $split,
                'tenant' => $tenant
            ];
            
            $notification = new PledgeEmailNotification();
            $notification->run('created', $args);
            unset($notification);
        }
    }
    
    public function updated(TransactionTemplateSplit $split) {
        
    }
    
    public function deleted(TransactionTemplateSplit $split) {
        
    }
}
