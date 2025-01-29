<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Requests\Pledges;
use App\Models\Campaign;
use App\Models\Form;
use App\Models\PledgeForm;
use App\Models\Purpose;
use App\Traits\Transactions\Transactions as TransactionsTrait;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PledgeFormsController extends Controller
{
    use TransactionsTrait;
    const PERMISSION = 'crm-pledges';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(auth()->check()){   // allows for public rotues/methods (e.g., share, submit)
                if(!auth()->user()->tenant->can(self::PERMISSION)){
                    return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
                }
            }
            return $next($request);
        });
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $forms = PledgeForm::all();
        $data = [
            'forms' => $forms,
            'total' => $forms->count(),
            'nextOrder' => '',
            'sort' => ''
        ];
        return view('pledges.forms.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('pledge-create')) abort(402);
        
        $charts = Purpose::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $campaigns = Campaign::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $forms = Form::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $data = [
            'charts' => $charts,
            'campaigns' => $campaigns,
            'forms' => $forms,
            'form' => null
        ];
        
        
        return view('pledges.forms.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Pledges\StorePledgeForm $request)
    {
        $form = mapModel(new PledgeForm(), $request->all());
        array_set($form, 'uuid', Uuid::uuid4());
        array_set($form, 'description', array_get($request, 'content'));
        if( auth()->user()->tenant->pledgeForms()->save($form) ){
            return redirect()->route('pledgeforms.edit', ['id' => array_get($form, 'id')])->with('message', __('Pledge Form created succesfully'));
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $form = PledgeForm::findOrFail($id);
        $data = ['form' => $form];
        return view('pledges.forms.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $form = PledgeForm::findOrFail($id);
        $charts = Purpose::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $campaigns = Campaign::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $forms = Form::all()->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, []);
        
        $data = [
            'form' => $form,
            'charts' => $charts,
            'campaigns' => $campaigns,
            'forms' => $forms
        ];
        
        return view('pledges.forms.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Pledges\Update $request, $id)
    {
        $form = PledgeForm::findOrFail($id);
        mapModel($form, $request->all());
        array_set($form, 'description', array_get($request, 'content'));
        if( $form->update() ){
            return redirect()->route('pledgeforms.edit', ['id' => $id])->with('message', __('Pledge Form updated succesfully'));
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        PledgeForm::destroy($id);
        return redirect()->route('pledgeforms.index')->with('message', __('Pledge Form deleted succesfully'));
    }
    
    public function share($uuid, Request $request) {
        
        $form = PledgeForm::where('uuid', $uuid)->first();
        if (is_null($form)) {
            abort(404);
        }
        $data = [
            'form' => $form
        ];
        return view('pledges.forms.public.show')->with($data);
    }

    public function submit($uuid, Pledges\PledgeFormSubmit $request) {
        $form = PledgeForm::where('uuid', $uuid)->first();
        if (is_null($form)) {
            abort(404);
        }
        
        \App\Classes\Redirections::store($request);
        
        $recurring = (bool) array_get($request, 'is_recurring');
        if ($request->has('is_recurring') && !$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
        }
        else{
            $fields = $request->all();
        }
        
        array_set($fields, 'type', 'donation');
        array_set($fields, 'status', 'pledge');
        array_set($fields, 'is_pledge', true);
        array_set($fields, 'campaign_id', array_get($form, 'campaign_id'));
        array_set($fields, 'purpose_id', array_get($form, 'purpose_id'));
        array_set($fields, 'tenant_id', array_get($form, 'tenant_id'));
        array_set($fields, 'status', 'pledge');
        
        $result = $this->processTransactionStore($fields, true);
        
        if (!is_null($result)) {
            if (array_get($form, 'form_id', 1) > 1) {//redirect to form fill
                $params = [
                    'id' => array_get($form, 'form.uuid'),
                    'cid' => array_get($fields, 'contact_id')
                ];
                return redirect()->route('forms.share', $params)->with('form-message', 'Pledge succesfully added, now please fill the following form');
            }
            $redirect = \App\Classes\Redirections::get();
            
            return redirect($redirect)->with('message', 'Pledge succesfully added');
        }
        abort(500);
    }
}
