<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Classes\Email\Mailgun;

use App\Models\Settings\SettingValue;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Models\TransactionTemplate;
use App\Models\Contact;
use App\Models\Settings\Setting;
use App\Classes\Email\EmailQueue;
use App\Models\TransactionTemplateSplit;
use App\Models\Purpose;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Tenant;

/**
 * Description of PledgeReminders
 *
 * @author josemiguel
 */
class PledgeEmailNotification {

    public function run($notification = 'reminder', $args = []) {
        if ($notification === 'reminder') {
            $this->reminder();
        }

        if ($notification === 'canceled') {
            $this->canceled($args);
        }

        if ($notification === 'created') {
            $this->created($args);
        }

        if ($notification === 'pledge-transaction-created') {
            $this->pledgeTransactionCreated($args);
        }
    }

    private function pledgeTransactionCreated($args) {
        $tenant = Tenant::withoutGlobalScopes()->where('id', array_get($args, 'transaction.tenant_id'))->first();
        $contact = array_get($args, 'transaction.contact');
        $chart = array_get($args, 'transaction.template.splits.0.chartOfAccount');
        $campaign = array_get($args, 'transaction.template.splits.0.campaign');
        $split = array_get($args, 'transaction.splits.0');
        $template = array_get($args, 'transaction.template');
        $pledge = array_get($args, 'transaction.pledge.0.splits.0');
        $ids = array_pluck(array_get($pledge, 'template.pledgedTransactions'), 'id');
        
        $data = [
            'contact' => $contact,
            'chart' => $chart,
            'campaign' => $campaign,
            'split' => $split,
            'template' => $template,
            'pledge' => $pledge
        ];
        
        if (!is_null($contact)) {
            $settings = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT')->first();
            
            if (is_null(array_get($settings, 'value'))) {
                $content = view()->make('emails.send.pledges.contact.pledge-transaction-created', $data)->render();
                $args = [
                    'from_name' => array_get($tenant, 'organization'),
                    'from_email' => array_get($tenant, 'email'),
                    'subject' => 'Thank you for your gift',
                    'content' => $content,
                    'model' => $contact,
                    'queued_by' => 'pledge.transaction.created'
                ];
                EmailQueue::set($contact, $args);
            }
            
            $settings = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN')->first();
            if (is_null(array_get($settings, 'value'))) {
                $admin = User::where('tenant_id', array_get($contact, 'tenant_id'))->first();
                array_set($data, 'admin', $admin);
                $content = view()->make('emails.send.pledges.admin.pledge-transaction-created', $data)->render();
                
                $args = [
                    'from_name' => array_get($tenant, 'organization'),
                    'from_email' => array_get($tenant, 'email'),
                    'subject' => 'A payment was made toward a pledge',
                    'content' => $content,
                    'model' => $admin,
                    'queued_by' => 'pledge.transaction.created'
                ];
                EmailQueue::set(array_get($admin, 'contact'), $args);
            }
        }
    }

    private function created($args) {
        $tenant = array_get($args, 'tenant');
        $contact = array_get($args, 'contact');
        $chart = array_get($args, 'chart');
        $campaign = array_get($args, 'campaign');
        $split = array_get($args, 'split');
        $template = array_get($split, 'template');
        $pledge = $split;
        $subject = array_get($campaign, 'id', 1) > 1 ? array_get($campaign, 'name') : array_get($chart, 'name');
        
        $data = [
            'contact' => $contact,
            'chart' => $chart,
            'campaign' => $campaign,
            'split' => $split,
            'template' => $template,
            'pledge' => $pledge,
            'admin' => array_get($args, 'user')
        ];
            
        $settings = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_CONTACT')->first();
        if (is_null(array_get($settings, 'value'))) {
            $content = view()->make('emails.send.pledges.contact.pledge-created', $data)->render();
            $values = [
                'from_name' => array_get($tenant, 'organization'),
                'from_email' => array_get($tenant, 'email'),
                'subject' => 'Thanks for supporting '. $subject,
                'content' => $content,
                'model' => array_get($args, 'contact'),
                'queued_by' => 'pledges.created'
            ];
            EmailQueue::set(array_get($args, 'contact'), $values);
        }

        $settings = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN')->first();
        if (is_null(array_get($settings, 'value'))) {
            $content = view()->make('emails.send.pledges.admin.pledge-created', $data)->render();
            $values = [
                'from_name' => array_get($tenant, 'organization'),
                'from_email' => array_get($tenant, 'email'),
                'subject' => 'A new pledge was created for ' . $subject,
                'content' => $content,
                'model' => array_get($args, 'user'),
                'queued_by' => 'pledges.created'
            ];
            $contact = array_get($args, 'user.contact');
            EmailQueue::set($contact, $values);
        }
    }

    

    private function getPledges($ids) {
        $pledges = TransactionTemplate::withoutGlobalScopes()
                ->where('is_pledge', true)
                ->whereNotIn('status', ['complete', 'canceled'])
                ->whereIn('tenant_id', $ids)
                ->get();

        return $pledges;
    }

    private function canceled($args) {
        $contact = array_get($args, 'contact');
        $contents = array_get($args, 'content');
        $pledge = array_get($args, 'pledge');
        /*
          $email_id = $this->setEmailContents($content, $contact, $pledge, 'Pledge Canceled', 'pledges.canceled');
          $this->setEmailQueue($contact, $email_id);
         * 
         */
        $values = [
            'subject' => 'Pledge Canceled',
            'content' => $contents,
            'model' => $pledge,
            'queued_by' => 'pledges.canceled'
        ];
        EmailQueue::set($contact, $values);
    }

    private function reminder() {
        $reminderSetting = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH')->first();
        $discard = $reminderSetting->values()->withoutGlobalScopes()->where('value', '0')->get();
        $tenants = Tenant::withoutGlobalScopes()->whereNotIn('id', array_pluck($discard, 'tenant_id'))->get();
        $ids = array_pluck($tenants, 'id');
        $pledges = $this->getPledges($ids);
        foreach ($pledges as $pledge) {
            $this->sendReminder($pledge);
        }
    }

    private function sendReminder($pledge) {
        $split = TransactionTemplateSplit::withoutGlobalScopes()->where('transaction_template_id', array_get($pledge, 'id'))->first();
        $chart = Purpose::withoutGlobalScopes()->where('id', array_get($split, 'purpose_id'))->first();
        $chartAltID = $chart->altIds()->withoutGlobalScopes()->first();
        $campaign = Campaign::withoutGlobalScopes()->where('id', array_get($split, 'campaign_id'))->first();
        $campaignAltID = $campaign->altIds()->withoutGlobalScopes()->first();
        
        $data = [
            'template' => $pledge,
            'pledge' => $split,
            'chart' => $chart,
            'campaign' => $campaign,
            'chartAltID' => $chartAltID,
            'campaignAltID' => $chartAltID
        ];
                
        $view = View::make('emails.send.pledge-reminder', $data);
        $contents = $view->render();
        
        $timestamp = strtotime(array_get($pledge, 'billing_start_date', '2000-01-01'));
        $promised_pay_date = Carbon::createFromTimestamp($timestamp);
        $diff_promised_pay_date = Carbon::now()->diffInDays($promised_pay_date);

        $setting = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_TEXT_EVERY')->first();
        $value = $setting->value()->withoutGlobalScopes()->where('tenant_id', array_get($pledge, 'tenant_id'))->first();
        $every_number_of_days = (int) array_get($value, 'value', '5');

        $setting = Setting::withoutGlobalScopes()->where('key', 'PLEDGE_EMAIL_REMINDER_TEXT_STARTING')->first();
        $value = $setting->value()->withoutGlobalScopes()->where('tenant_id', array_get($pledge, 'tenant_id'))->first();
        $days_before_promised_pay_date = (int) array_get($value, 'value', '20');

        $timestamp = strtotime(array_get($pledge, 'last_reminder_sent_at', '2000-01-01'));

        $last_reminder_sent_at = Carbon::createFromTimestamp($timestamp);
        $diff_last_reminder_sent_at = Carbon::now()->diffInDays($last_reminder_sent_at);

        if ($diff_promised_pay_date <= $days_before_promised_pay_date && $diff_last_reminder_sent_at >= $every_number_of_days) {
            $contact = Contact::withoutGlobalScopes()->where('id', array_get($pledge, 'contact_id'))->first();
            $tenant = Tenant::withoutGlobalScopes()->where('id', array_get($contact, 'tenant_id'))->first();
            $values = [
                'from_name' => array_get($tenant, 'organization'),
                'from_email' => array_get($tenant, 'email'),
                'subject' => 'Pledge Reminder',
                'content' => $contents,
                'model' => $pledge,
                'queued_by' => 'pledges.reminder'
            ];
            
            EmailQueue::set($contact, $values);

            DB::table('transaction_templates')
                    ->where('id', array_get($pledge, 'id'))
                    ->update(['last_reminder_sent_at' => Carbon::now()]);
        }
    }

}
