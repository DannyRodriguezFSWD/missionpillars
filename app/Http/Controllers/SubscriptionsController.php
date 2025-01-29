<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Classes\MissionPillarsLog;
use App\Models\Feature;
use App\Models\Module;
use App\Models\MPInvoice;
use App\Models\PaymentOption;
use App\Models\Promocode;
use App\Traits\CountriesTrait;
use App\Traits\ModuleTrait;
use App\Traits\SendsCustomerServiceEmails;
use App\Traits\UpdatesPaymentOptions;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use function GuzzleHttp\json_encode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Trexology\Promocodes\Model\Promocodes;

class SubscriptionsController extends Controller
{
    use CountriesTrait;
    use ModuleTrait;
    // use SendsCustomerServiceEmails;
    // use UpdatesPaymentOptions;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $option = array_get($request, 'feature');
        $module = Module::whereHas('features', function($query) use($option){
            $query->where('name', $option);
        })->first();
        $chms = Module::find(2);
        $acct = Module::find(3);

        $feature = Feature::where('name', $option)->first();
        $amount_unpaid = MPInvoice::unpaid()->sum('total_amount');

        $promocodes = $this->getAutoAppliedPromocodes();
        $discounts = $this->getDiscounts($promocodes);
        
        $data = compact('module','feature','chms','acct','amount_unpaid','promocodes','discounts');
        return view('settings.subscriptions.index')->with($data);
    }

    public function getModules(Request $request){
        if($request->ajax()){
            //dd($request->all());
            $installed = auth()->user()->tenant->modules()->with(['features'])->get();
            $available = Module::whereNotIn('id', array_pluck($installed, 'id'))->whereNull('deleted_at')->get();
            $response = [
                'modules' => [
                    'installed' => $installed,
                    'available' => $available,
                    'all' => Module::all()
                ],
                'countries' => $this->getCountriesAsArrayObjects(['name', 'iso_3166_2'], true),
                'canClaimFreeMonth' => array_get(auth()->user(), 'tenant.free_month', false),
                'canClaimAccountingFreeMonth' => array_get(auth()->user(), 'tenant.accounting_free_month', false),
                'billing_start_at' => Carbon::now()->addDays(Constants::MODULE_FREE_DAYS)->addDay()->toFormattedDateString()
            ];
            //dd($response);
            return response()->json($response);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        return view('settings.subscriptions.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $module = auth()->user()->tenant->modules()->where('module_id', $id)->first();
        $tenant = auth()->user()->tenant;
        
        if(!is_null(array_get($tenant, 'last_billing_at'))){
            $last_billing_at = Carbon::parse(array_get($tenant, 'last_billing_at'));
        }
        else{
            $last_billing_at = Carbon::parse(array_get($module, 'pivot.created_at'));
        }
        $today = Carbon::now();

        $result = $tenant->billingModule($module, $tenant, $last_billing_at, $today);
        $tenant->createInvoice([$result]);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        if(!$request->ajax()){
            // handle error?
        }
        $tenant = auth()->user()->tenant;
        $module = Module::findOrFail($id); // prevents activation of fake module
        $modulename = null;
        if ($id == 2) $modulename = 'CRM';
        elseif ($id == 3) $modulename = 'Accounting';
        else $modulename = false;
        
        if( array_get($request, 'action') == 'enable' ){
            
            if ($tenant->unpaidInvoices()->count()) {
                $success = $this->payUnpaidInvoices($tenant);
                if (!$success) return response()->json(false);
                
                // If we have not re-activated the requested module, simply enable it
                if (!$tenant->modules()->where('module_id',$id)->count()) {
                    $this->enableModule($id, $request);
                }
            } else {
                $this->enableModule($id, $request);
            }
        } else { // if not enabling, cancellation has been requested
            
            if(!is_null($tenant)){
                //prepare cancel module, so nextime billig runs
                //it will bill the days they used then will cancel module
                //right now we are going to cancel manually
                /*
                $tenant->modules()->updateExistingPivot($id, [
                    'next_billing_at' => Carbon::now()->midDay(),
                    'cancel' => true
                ]);
                */
                $tenant->modules()->updateExistingPivot($id, [
                    'cancelation_requested_at' => Carbon::now(),
                ]);

                //we send email
                //customerservice@continuetogive.com
                $email_message = array_get($request, 'message');
                $subject = 'Subscription cancellation';
                
                $replyTo = [
                    'name' => env('SALESPERSON'),
                    'email' => env('SALES_EMAIL')
                ];
                
                $this->sendCustomerServiceEmail($subject, $email_message, compact('tenant', 'module', 'replyTo'));
            }
        }
        
        return response()->json(true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function checkPaymentInfo(Request $request){
        return response()->json($this->getStripePaymentOptions());
    }
    
    /** 
     * TODO consider moving to a trait and implementing per tenant instead of per client
     */
    private function getStripePaymentOptions() {
        if(!auth()->check()) return collect([]);
        
        return auth()->user()->tenant->paymentOptions()->stripe()->get();
    }

    public function saveCreditCardInfo(Request $request)
    {
        $customer = null;
        
        // TODO when payment info is updated check for any necessary backpay
        $date = implode('/', [array_get($request, 'cc.month'), '1', array_get($request, 'cc.year')]);
        $expiration = Carbon::parse($date);
        
        if (!auth()->check()) {
            return response('Session expired. You have to login first', 400); 
        }
        
        $user = auth()->user();
        if(!$user->hasStripeId()){
            $user->createAsStripeCustomer(array_get($request, 'stripe.id'));
            $customer = $user->asStripeCustomer();
        }
        else{
            $customer = $user->asStripeCustomer();
            try {
                $customer->sources->create(["source" => array_get($request, 'stripe.id')]);
            } catch (\Exception $e) {
                $stripeerror = $e->getJsonBody()['error'];
                return response( $stripeerror['message'], 400 );
            }
        }

        
        $card = $customer->sources->retrieve(array_get($request, 'stripe.card.id'));
        
        if (empty($card)) {
            return response('Card not found, please try again', 400); 
        }
        
        $card->name = implode(' ', [
            array_get(auth()->user(), 'contact.first_name', array_get(auth()->user(), 'name')),
            array_get(auth()->user(), 'contact.last_name', array_get(auth()->user(), 'last_name')),
        ]);
        //$card->first_four = NULL;
        $card->save();
        
        $payment_option = new PaymentOption();
        array_set($payment_option, 'category', 'cc');
        array_set($payment_option, 'card_id', array_get($request, 'stripe.card.id'));
        array_set($payment_option, 'card_type', array_get($request, 'stripe.card.brand'));
        array_set($payment_option, 'card_expiration', $expiration->toDateString());
        //array_set($card, 'first_four', substr(array_get($request, 'cc.number'), 0, 4));
        array_set($payment_option, 'last_four', $card->last4);
        array_set($payment_option, 'contact_id', array_get(auth()->user(), 'contact.id'));
        array_set($payment_option, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
        $payment_option->save();
        
        $this->setDefaultPaymentOption($payment_option);
    }

    public function paymentOptions(Request $request){
        $options = $this->getStripePaymentOptions();

        $result = collect($options)->reduce(function($carry, $item){
            $card = [
                'id' => array_get($item, 'id'),
                'card_type' => array_get($item, 'card_type'),
                'last_four' => array_get($item, 'last_four'),
                'selected' => array_get($item, 'id') == array_get(auth()->user(), 'tenant.payment_option_id')
            ];
            array_push($carry, $card);
            return $carry;
        }, []);
        
        return response()->json($result);
    }

    public function updatePaymentOption(Request $request){
        if(array_has($request, 'payment_option_id')){
            $cardvalues = (object) $request->get('card');
            $payment_option_id = $request->get('payment_option_id');
            $customer = auth()->user()->asStripeCustomer();
            
            $card = $this->updatePaymentOptionExpiration($customer, $payment_option_id, $cardvalues);
        }
        else{
            $card = PaymentOption::findOrFail(array_get($request, 'id'));
            $this->setDefaultPaymentOption($card, auth()->user());
        }
        
        return response()->json($card);
    }

    public function deletePaymentOption(Request $request){
        $this->removeStripePaymentOption(auth()->user(), array_get($request, 'id'));
        return response()->json($request->all());
    }

    

    public function invoicesInfo(Request $request){
        $data = [
            'invoices' => MPInvoice::orderBy('id', 'desc')->paginate()
        ];
        return view('settings.subscriptions.invoices')->with($data);
    }

    public function downloadInvoice($id, Request $request){
        $invoice = MPInvoice::findOrFail($id);

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
