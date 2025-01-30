<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Classes\Email\Mailgun\PledgeEmailNotification;
use App\Models\Email;
use App\Models\Family;
use App\Models\FormEntry;
use App\Models\Form;
use App\Models\EmailSent;
use App\Models\Purpose;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Models\User;
use App\Models\Contact;
use App\Classes\Commands\Pledges\UpdatePromisedPayDate;
use Illuminate\Support\Facades\DB;
use App\Classes\Transactions;
use App\Classes\MissionPillarsLog;
use App\Classes\Email\Mailgun\Send;
use App\Classes\Email\Mailgun\MailgunRuntime;
use Carbon\Carbon;
use App\Classes\ContinueToGive\ContinueToGiveCampaigns;
use App\Classes\ContinueToGive\ContinueToGiveMissionaries;
use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Classes\Twilio\TwilioAPI;
use App\Models\SMSPhoneNumber;
use App\Classes\Twilio\TwilioSender;
use App\Models\MPInvoice;
use App\Traits\MassMessageTrait;
use App\Traits\ModuleTrait;
use App\Classes\Commands\Billing\Billing;
use Barryvdh\DomPDF\Facade as PDF;
use Stripe\Stripe;
use Stripe\Customer;
use App\Constants;
use App\Models\Register;
use App\Classes\Email\CheckinAlert;
use App\Classes\Email\Mailgun\Status as EmailStatus;
use App\Traits\AlternativeIdTrait;
use App\Traits\Transactions\PurposeTrait;
use Maatwebsite\Excel\Facades\Excel;

class TestsController extends BaseController {
    use MassMessageTrait, ModuleTrait, AlternativeIdTrait, PurposeTrait;

    function __construct() {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $by_funds = false;
        $json = '[{"chart_of_account":"asset","account":10,"name":"asset account 1","number":1001,"fund":2,"fund_name":"starting fund","balance":11},{"chart_of_account":"equity","account":12,"name":"starting fund Fund Balance","number":3000,"fund":2,"fund_name":"starting fund","balance":100},{"chart_of_account":"equity","account":13,"name":"fund for nala Fund Balance","number":3001,"fund":3,"fund_name":"fund for nala","balance":20},{"chart_of_account":"asset","account":14,"name":"asset account 2","number":1002,"fund":3,"fund_name":"fund for nala","balance":3},{"chart_of_account":"equity","account":16,"name":"funds for pato Fund Balance","number":3002,"fund":4,"fund_name":"funds for pato","balance":15},{"chart_of_account":"liability","account":17,"name":"liability account 1","number":2001,"fund":4,"fund_name":"funds for pato","balance":-6}]';
        $asset_liability_groups = json_decode($json);
        dd($asset_liability_groups);
        foreach ($asset_liability_groups as $group) {
            //$key = $group->chart_of_account === 'asset' ? 'assets' : $group->chart_of_account;
            $amount = $group->balance;

            if($group->chart_of_account === 'asset'){
                $key = 'assets';
                foreach ($asset_liability_groups as $balance) {

                    if($balance->chart_of_account == 'equity' && $balance->fund == $group->fund){
                        $amount = $amount + $balance->balance;
                    }
                }
            }
            else{
                $key = $group->chart_of_account;
            }

            $data = [
                'name' => $group->name,
                'balance' => $amount,
                'number' => intval($group->number),
                'fund_name' => $group->fund_name,
            ];
            if ($by_funds) {
                $data['fund'] = $group->fund;
            }

            $report[$key][$group->account . "-" . $group->fund] = $data;
        }
        dd("stop", $report);
        /*
        $invoice = MPInvoice::findOrFail(15);

        if(auth()->check()){
            $tenant = auth()->user()->tenant;
        }

        $data = [
            'invoice' => $invoice,
            'tenant' => $tenant
        ];

        //return view('settings.subscriptions.pdf_invoice')->with($data);
        $filename = 'Invoice_' . array_get($invoice, 'reference').'.pdf';
        $pdf = PDF::loadView('settings.subscriptions.pdf_invoice', $data);
        return $pdf->download($filename);
        */
        //$billing = new Billing();
        //$billing->run();
        /*
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer = Customer::retrieve("cus_DxggpNN2PCvsco");
        $card = $customer->sources->retrieve("card_1DW9TiB75D07z04h0hyt6Hx5");
        dd($customer, $card);
        */
        abort(404);
        //return view('test');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $registry = Register::findOrFail(25);
        //$registry->splits()->delete();
        $split = $registry->splits()->first();
        array_set($split, 'updated_at', Carbon::now());
        $split->delete();
        dd('stop');
        if (env('APP_MODE') === 'developer') {
            if($id == 'update-form-entry-relationship'){
                $entries = FormEntry::withoutGlobalScopes()->whereNotNull('contact_id')->orWhereNotNull('transaction_id')->get();

                foreach ($entries as $entry) {
                    if(!is_null(array_get($entry, 'transaction_id'))){//link payer
                        $transaction = $entry->transaction()->withoutGlobalScopes()->where('id', array_get($entry, 'transaction_id'))->first();

                        DB::table('contact_entry')->insert([
                            [
                                'form_entry_id' => array_get($entry, 'id'),
                                'contact_id' => array_get($transaction, 'contact_id'),
                                'relationship' => array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER')
                            ]
                        ]);
                    }

                    if(!is_null(array_get($entry, 'contact_id'))){//link contact
                        DB::table('contact_entry')->insert([
                            [
                                'form_entry_id' => array_get($entry, 'id'),
                                'contact_id' => array_get($entry, 'contact_id'),
                                'relationship' => array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT')
                            ]
                        ]);
                    }
                }
                return response("OK", 200);
            }

            if ($id === 'set-contact-uuid') {
                $contacts = \App\Models\Contact::withoutGlobalScopes()->withTrashed()->whereNull('uuid')->get();
                foreach ($contacts as $contact) {
                    //array_set($contact, 'uuid', \Ramsey\Uuid\Uuid::uuid4());
                    echo array_get($contact, 'first_name') . '<br>';
                    //$contact->update();
                    \Illuminate\Support\Facades\DB::table('contacts')
                            ->where('id', array_get($contact, 'id'))
                            ->update([
                                'uuid' => \Ramsey\Uuid\Uuid::uuid4()
                    ]);
                }
                return response("OK", 200);
            }

            if ($id === 'move-email-tags') {
                $this->moveEmailTags();
                return response("OK", 200);
            }

            if ($id === 'move-email-tags-again') {
                $this->moveEmailTagsAgain();
                return response("OK", 200);
            }

            if ($id === 'import-c2g-data') {
                $this->importC2GData();
                return response("OK", 200);
                //return redirect()->route('dashboard.index');
            }

            if ($id === 'send-email-queue') {
                $this->sendEmailQueue();
                return response("OK", 200);
            }

            if ($id === 'test-mailgun-runtime') {
                MailgunRuntime::setOutgoingDomain(1);
            }

            if ($id === 'widgets') {
                $widget = \App\Models\Widget::findOrFail(52);
                $chart = \App\Classes\Shared\Widgets\Charts\Line\LineChart::fundraisingMetrics($widget);
                dd($chart);
            }

            if($id === 'update-chart-of-accounts'){
                return $this->updatePurposes();
            }

            if($id == 'tenant-types'){
                $this->tenantTypes();
                return response("OK", 200);
            }

            if($id === 'tag-contacts'){
                $contacts = Contact::whereIn('id', [
                    32,
                    33,
                    34,
                    377,
                    1311,
                    25289,
                    25389,
                    25393,
                    25394,
                    25487,
                    25488,
                    25527,
                    25550,
                    25551,
                    25553,
                    25554,
                    25555,
                    25564,
                    25568,
                    25969,
                    26505,
                    26509,
                    26531
                ])->get();

                foreach($contacts as $contact){
                    $contact->tags()->sync(10, false);
                }
            }

            if($id == 'start-billing-plan'){
                $tenants = Tenant::all();
                $date = Carbon::now();
                foreach($tenants as $tenant){
                    if(env('APP_ENV') != 'production'){
                        $next_date = Carbon::now()->addYear()->endOfYear();
                        $modules = [1, 2, 3];
                        array_set($tenant, 'chms_app_fee', 50);
                        array_set($tenant, 'accounting_app_fee', 25);
                        array_set($tenant, 'phone_number_fee', 10);
                        array_set($tenant, 'sms_fee', 0.20);
                        array_set($tenant, 'email_fee', 0.01);
                        array_set($tenant, 'contact_fee', 0.02);
                    }
                    else{
                        $next_date = Carbon::now()->endOfMonth();
                        $modules = [1];
                    }

                    $tenant->modules()->sync($modules, false);
                    array_set($tenant, 'start_billing_at', $date);
                    array_set($tenant, 'next_billing_at', $next_date);
                    array_set($tenant, 'last_billing_at', $date);
                    $tenant->update();
                }
                return response('ok');
            }

            if($id == 'login'){
                auth()->loginUsingId(array_get($request, 'id'));
                return response('OK');
            }
        }

        abort(404);
    }

    private function tenantTypes() {
        $tenants = Tenant::withoutGlobalScopes()->get();
        foreach ($tenants as $tenant) {
            $user = $tenant->users()->first();
            if (!is_null($user)) {
                if ($user->hasRole('organization-owner')) {
                    auth()->loginUsingId(array_get($user, 'id'));
                    if (auth()->check()) {
                        $integration = auth()->user()->tenant->integrations()->where('service', 'Continue to Give')->first();
                        if (!is_null($integration)) {
                            $value = $integration->values()->where('key', 'API_KEY')->first();
                            $alt = \App\Models\AltId::withoutGlobalScopes()->where([
                                'relation_id' => array_get($tenant, 'id'),
                                'relation_type' => Tenant::class,
                                'tenant_id' => array_get($tenant, 'id')
                            ])->first();
                            //api call
                            $campaigns = new ContinueToGiveCampaigns(array_get($value, 'value'));
                            $campaigns->getTenantType(['id' => array_get($alt, 'alt_id')]);
                        }
                    }
                }
            }
        }
    }

    private function updatePurposes(){
        $tenants = Tenant::withoutGlobalScopes()->get();
        foreach ($tenants as $tenant) {
            $user = $tenant->users()->first();
            if (!is_null($user)) {
                if ($user->hasRole('organization-owner')) {
                    auth()->loginUsingId(array_get($user, 'id'));
                    if (auth()->check()) {
                        $integration = auth()->user()->tenant->integrations()->where('service', 'Continue to Give')->first();
                        if (!is_null($integration)) {
                            $value = $integration->values()->where('key', 'API_KEY')->first();
                            $campaigns = new ContinueToGiveCampaigns(array_get($value, 'value'));
                            $campaigns->run();

                            $missionaries = new ContinueToGiveMissionaries(array_get($value, 'value'));
                            $missionaries->run();
                        }
                    }
                }
            }
        }
        return response('OK', 200);
    }

    private function sendEmailQueue() {
        $send = new Send();
        $send->run();
    }

    private function importC2GData() {
        $tenants = Tenant::withoutGlobalScopes()->get();
        foreach ($tenants as $tenant) {
            $user = $tenant->users()->first();
            if (!is_null($user)) {
                if ($user->hasRole('organization-owner')) {
                    auth()->loginUsingId(array_get($user, 'id'));
                    if (auth()->check()) {
                        $integration = auth()->user()->tenant->integrations()->where('service', 'Continue to Give')->first();
                        if (!is_null($integration)) {
                            $value = $integration->values()->where('key', 'API_KEY')->first();
                            if (!is_null($value)) {
                                $transactions = new Transactions(array_get($value, 'value'));
                                $transactions->executeTransactions();
                            }
                        }
                    }
                }
            }
        }
    }

    private function moveEmailTagsAgain() {
        $emails = Email::all();
        foreach ($emails as $email) {
            $include = DB::table('email_tags')->where([
                        ['email_content_id', '=', array_get($email, 'id')],
                        ['action', '=', 'include']
                    ])->get();
            $tags = array_pluck($include, 'tag_id');
            $email->includeTags()->sync($tags);


            $exclude = DB::table('email_tags')->where([
                        ['email_content_id', '=', array_get($email, 'id')],
                        ['action', '=', 'exclude']
                    ])->get();
            $tags = array_pluck($exclude, 'tag_id');
            $email->excludeTags()->sync($tags);
        }
    }

    private function moveEmailTags() {
        $emails = Email::withoutGlobalScopes()->get();
        foreach ($emails as $email) {
            $tenant = Tenant::withoutGlobalScopes()->where('id', array_get($email, 'tenant_id'))->first();
            if (!is_null(array_get($email, 'list_id'))) {
                array_set($email, 'from_name', array_get($tenant, 'organization'));
                array_set($email, 'from_email', array_get($tenant, 'email'));
                $email->update();

                if (!is_null(array_get($email, 'exclude_tags'))) {
                    $exclude = explode(',', array_get($email, 'exclude_tags'));
                    $exclude = array_diff($exclude, [""]);
                    $pivot = array_fill(0, count($exclude), ['action' => 'exclude']);
                    $sync = array_combine($exclude, $pivot);
                    try {
                        $email->excludeTags()->sync($sync);
                    } catch (\Illuminate\Database\QueryException $ex) {

                    }
                }
            } else {
                $user = User::withoutGlobalScopes()->where('tenant_id', array_get($tenant, 'id'))->first();
                $contact = Contact::withoutGlobalScopes()->where('user_id', array_get($user, 'id'))->first();
                array_set($email, 'from_name', array_get($contact, 'first_name') . ' ' . array_get($contact, 'last_name'));
                array_set($email, 'from_email', array_get($contact, 'email_1'));
                $email->update();
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function test()
    {
        abort(404);
        
        // Comment line above and run this from your browser to test something
    }
    
    public function testCreateSalesmateActivity()
    {
        abort(404);
        
        $tenant = Tenant::findOrFail(2);
        $message = 'Billing failed';
        $moduleName = 'CRM';
        
        $salesmate = new \App\Classes\Salesmate\Salesmate();
        $salesmate->createBillingFailedActivity($tenant, $message, $moduleName);
    }
    
    public function testChargeInvoice($id)
    {
        abort(404);
        
        $this->chargeInvoice($id);
    }
    
    public function testReleaseTickets()
    {
        abort(404);
        
        \App\Classes\Events\EventSignin::releaseTickets();
    }
    
    public function testSendSMS()
    {
        abort(404);
        
        $json = '{"data":{"id":13,"page":2,"pages":1,"type":"sms","audience":{"send_all":false,"send_number_of_messages":1,"do_not_send_within_number_of_days":5},"message":{"test":false,"test_phone_number":"","sms_phone_number_id":26,"list_id":0,"datatable_state_id":0,"content":"a"},"tags":{"include":[],"exclude":[]},"actions":[{"input":"message_sent","text":"Message sent","tag":0},{"input":"message_delivered","text":"Message delivered","tag":0},{"input":"message_undelivered","text":"Message undelivered","tag":0},{"input":"message_failed","text":"Message failed","tag":0}]}}';
        $request = json_decode($json, true);
        $settings = array_get($request, 'data');

        $sms = $this->storeSettings($settings);
        $this->queue($request, $sms);
    }
    
    public function testSMSReply()
    {
        abort(404);
        
       
        $json = '{"ToCountry":"US","ToState":"GA","SmsMessageSid":"12312312312312312312","NumMedia":"0","ToCity":"ATLANTA","FromZip":null,"SmsSid":"123213123123123","FromState":"Oax.","SmsStatus":"received","FromCity":"Zimatlan De Alvarez","Body":"Hola","FromCountry":"MX","To":"+15005550006","ToZip":null,"NumSegments":"1","MessageSid":"SMc8361bd39bdf8f0caa7cdbc8d3124240","AccountSid":"AC46fa212461064e808c176e74e0fc56a9","From":"+13123123123","ApiVersion":"2010-04-01"}';
        $request = json_decode($json, true);
        
        $twilio = new TwilioAPI();
        $twilio->replyReceivedCallback($request);
    }
    
    public function testBilling()
    {
        abort(404);
        
        $tenant = Tenant::findOrFail(2);
        
        foreach (array_get($tenant, 'modules') as $module) {
            $billing = $this->billModule($module, $tenant);
        
            dump($billing);
        }
        
//        $billing = new Billing();
//        $billing->run();
    }
    
    public function testCheckinAlert()
    {
        abort(404);
        
        $checkinAlert = new CheckinAlert();
        $checkinAlert->run();
    }
    
    public function createFamilies()
    {
        abort(404);
        
        $tenantId = null;
        
        if (empty($tenantId)) {
            abort(500);
        }
        
        $contacts = Contact::where('tenant_id', $tenantId)->whereNull('family_id')->get(['id'])->toArray();
        
        foreach ($contacts as $contact) {
            $contact = Contact::find(array_get($contact, 'id'));
            
            if (empty($contact)) {
                continue;
            }
            
            if (array_get($contact, 'family_id')) {
                continue;
            }

            if ($contact->relatives->count() === 0 && $contact->relativesUp->count() === 0) {
                continue;
            }
            
            $family = new Family;
            $family->tenant_id = array_get($contact, 'tenant_id');
            $family->name = 'The '.ucfirst(array_get($contact, 'last_name')).' Family';
            $family->save();
 
            $familyPosition = 'Other';
            
            // TODO head of household depreacted
            if (array_get($contact, 'head_of_household') == 1) {
                $familyPosition = 'Primary Contact';
            }
            
            if (!empty($contact->relatives)) {
                foreach ($contact->relatives as $relative) {
                    if (in_array(array_get($relative, 'pivot.contact_relationship'), ['Primary Contact', 'Other', 'Spouse', 'Child'])) {
                        $familyPosition = array_get($relative, 'pivot.contact_relationship');
                    }
                    
                    if (in_array(array_get($relative, 'pivot.relative_relationship'), ['Primary Contact', 'Other', 'Spouse', 'Child'])) {
                        $relative->family_id = array_get($family, 'id');
                        $relative->family_position = array_get($relative, 'pivot.relative_relationship');
                        $relative->update();
                    }
                }
            }
            
            if (!empty($contact->relativesUp)) {
                foreach ($contact->relativesUp as $relative) {
                    if (in_array(array_get($relative, 'pivot.relative_relationship'), ['Primary Contact', 'Other', 'Spouse', 'Child'])) {
                        $familyPosition = array_get($relative, 'pivot.relative_relationship');
                    }
                    
                    if (in_array(array_get($relative, 'pivot.contact_relationship'), ['Primary Contact', 'Other', 'Spouse', 'Child'])) {
                        $relative->family_id = array_get($family, 'id');
                        $relative->family_position = array_get($relative, 'pivot.contact_relationship');
                        $relative->update();
                    }
                }
            }
            
            $contact->family_id = array_get($family, 'id');
            $contact->family_position = $familyPosition;
            $contact->update();
        }
    }
    
    public function sendEmail()
    {
        abort(404);
        
        $send = new Send();
        $send->run();
    }
    
    public function sendSms()
    {
        abort(404);
        
        $send = new TwilioSender();
        $send->run();
    }
    
    public function checkTrial()
    {
        abort(404);
        
        $send = new Billing();
        $send->checkTrial();
    }
    
    public function trackEmail()
    {
        abort(404);
        
        $send = new EmailStatus();
        $send->run();
    }
    
    public function syncPurpose()
    {
        abort(404);
        
        $json = '{"data":[{"sync":"pages","alt_id":3501230,"page_type":"project","sub_type":"projects","name":"Test MP 1","description":"<p>test<\/p>","receive_donations":0,"goal":null,"highest_for_pageid":3500796,"contact":{"alt_id":3500796,"first_name":"Demo","last_name":"Admin","cell_phone":null,"email_1":null,"page_type":"profile","sub_type":"organizations"},"deleted_at":"2023-02-20 11:01:08"}],"meta":{"pagination":{"total":1,"count":1,"per_page":25,"current_page":1,"total_pages":1,"links":[]}}}';
        $data = json_decode($json, true);
        dump($data);
        
        $this->setSinglePurpose(array_get($data, 'data.0'));
    }
    
    public function uploadTransactionsCECP()
    {
        abort(404);
        
        $file = storage_path('/app/public/uploads/cecp_transactions.xlsx');
        $rawData = Excel::load($file)->get();
        $data = [];
        
        foreach ($rawData as $cur) {
            if (!array_get($cur, 'transaction_id')) {
                $name = array_get($cur, 'name');
            } else {
                array_set($cur, 'name', $name);
                $data[] = $cur;
            }
        }
        
        foreach ($data as $cecpTransaction) {
            $contact = Contact::where('first_name', array_get($cecpTransaction, 'name'))->first();
            
            if (empty($contact)) {
                $contact = Contact::where('preferred_name', array_get($cecpTransaction, 'name'))->first();
            }
            
            if (empty($contact)) {
                $contactData = [
                    'first_name' => array_get($cecpTransaction, 'name'),
                    'preferred_name' => array_get($cecpTransaction, 'name'),
                    'active' => 1,
                    'type' => 'person'
                ];
                
                $contact = new Contact();
                $contact = mapModel($contact, $contactData);

                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }
                
                $contact->refresh();
            }
            
            $altId = (int)array_get($cecpTransaction, 'transaction_id');

            if (empty($altId)) {
                continue;
            }

            $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);

            if (!empty($altIdObject)) {
                continue;
            }

            if (array_get($cecpTransaction, 'date')) {
                $transactionTime = Carbon::createFromFormat('m/d/Y', array_get($cecpTransaction, 'date'))->format('Y-m-d').' 12:00:00';

                if (!is_null($transactionTime)) {
                    // TODO - Find a better way to handle localization according to tenant local time
                    $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
                }
            } else {
                $transactionTime = null;
            }
            
            $transactionTemplateData = [
                'completion_datetime' => $transactionTime,
                'amount' => array_get($cecpTransaction, 'amount'),
                'is_recurring' => 0,
                'is_pledge' => 0,
                'successes' => 1,
                'acknowledged' => 0,
                'contact_id' => array_get($contact, 'id')
            ];

            $transactionData = [
                'transaction_initiated_at' => $transactionTime,
                'transaction_last_updated_at' => $transactionTime,
                'channel' => 'unknown',
                'check_number' => null,
                'system_created_by' => 'CECP',
                'status' => 'complete',
                'transaction_path' => 'cecp',
                'anonymous_amount' => 'protected',
                'anonymous_identity' => 'protected',
                'type' => 'donation',
                'acknowledged' => 0,
                'acknowledged_at' => null,
                'tax_deductible' => 1,
                'comment' => array_get($cecpTransaction, 'purpose'),
                'contact_id' => array_get($contact, 'id')
            ];

            $purpose = Purpose::where('sub_type', 'organizations')->first();
            
            $transactionSplitData = [
                'template' => [
                    'campaign_id' => 1,
                    'purpose_id' => array_get($purpose, 'id'),
                    'tax_deductible' => 1,
                    'type' => 'donation',
                    'amount' => array_get($cecpTransaction, 'amount'),
                    'splitAltId' => $altId
                ],
                'transaction' => [
                    'campaign_id' => 1,
                    'purpose_id' => array_get($purpose, 'id'),
                    'amount' => array_get($cecpTransaction, 'amount'),
                    'type' => 'donation',
                    'tax_deductible' => 1,
                    'splitAltId' => $altId
                ]
            ];
            
            // Transaction does not exist so we make a new one, else do nothing
            if (empty($altIdObject)) {
                $transactionTemplate = new TransactionTemplate();
                mapModel($transactionTemplate, $transactionTemplateData);

                if (!auth()->user()->tenant->transactionTemplates()->save($transactionTemplate)) {
                    abort(500);
                }

                $transactionTemplate->refresh();

                $this->alternativeIdCreate(array_get($transactionTemplate, 'id'), get_class($transactionTemplate), [
                    'alt_id' => $altId,
                    'label' => 'Transaction Template',
                    'system_created_by' => 'CECP'
                ]);

                $transactionData['transaction_template_id'] = array_get($transactionTemplate, 'id');

                $transaction = new Transaction();
                mapModel($transaction, $transactionData);

                if (!auth()->user()->tenant->transactions()->save($transaction)) {
                    abort(500);
                }

                $transaction->refresh();

                $this->alternativeIdCreate(array_get($transaction, 'id'), get_class($transaction), [
                    'alt_id' => $altId,
                    'label' => 'Transaction',
                    'system_created_by' => 'CECP'
                ]);

                $transactionSplitData['template']['transaction_template_id'] = array_get($transactionTemplate, 'id');
                $splitTemplate = new TransactionTemplateSplit();
                mapModel($splitTemplate, $transactionSplitData['template']);

                if (!auth()->user()->tenant->transactionTemplateSplits()->save($splitTemplate)) {
                    abort(500);
                }

                $splitTemplate->refresh();

                $this->alternativeIdCreate(array_get($splitTemplate, 'id'), get_class($splitTemplate), [
                    'alt_id' => $altId,
                    'label' => 'Transaction Template Split',
                    'system_created_by' => 'CECP'
                ]);

                $transactionSplitData['transaction']['transaction_id'] = array_get($transaction, 'id');
                $transactionSplitData['transaction']['transaction_template_split_id'] = array_get($splitTemplate, 'id');
                
                $split = new TransactionSplit();
                mapModel($split, $transactionSplitData['transaction']);

                if (!auth()->user()->tenant->transactionSplits()->save($split)) {
                    abort(500);
                }

                $split->refresh();

                $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), [
                    'alt_id' => $altId,
                    'label' => 'Transaction Split',
                    'system_created_by' => 'CECP'
                ]);
            }
        }
    }
    
    /**
     * Used to manually bill clients, specify which tenants to bill and what period
     */
    public function billClients()
    {
        abort(500);
        
        $today = '';
        
        if (!$today) {
            dump('No billing day.');
            abort(500);
        }
        
        $billingDay = Carbon::createFromFormat('Y-m-d H:i:s', $today);
        
        $billing = new Billing();
        $billing->run($billingDay);
        
        dd('done');
    }
}
