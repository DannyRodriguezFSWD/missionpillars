<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Twilio\TwilioAPI;
use App\Models\SMSPhoneNumber;
use App\Constants;

class SMSSettingsController extends Controller
{
    const PERMISSION = 'crm-communications';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $smsPhoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
            $data = $smsPhoneNumbers->map(function ($phone) {
                $phone->name = $phone->name_and_number;
                return $phone;
            });
            return response()->json($data);
        }
        
        if (!auth()->user()->can('settings-view')) abort(404);
        
        $smsPhoneNumbers = array_get(auth()->user(), 'tenant.SMSPhoneNumbers');
        return view('settings.sms.index')->with(compact('smsPhoneNumbers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('settings-view')) abort(404);
        
        return view('settings.sms.create');
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('settings-view')) abort(404);
        
        $smsPhoneNumber = SMSPhoneNumber::findOrFail($id);
        return view('settings.sms.edit')->with(compact('smsPhoneNumber'));
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
        $phone = SMSPhoneNumber::find($id);
        if(is_null($phone)){
            return reponse(204);
        }

        array_set($phone, 'name', array_get($request, 'name'));
        if (!empty(array_get($request, 'contacts'))) {
            array_set($phone, 'notify_to_contacts', implode(',', array_get($request, 'contacts')));
        } else {
            array_set($phone, 'notify_to_contacts', null);
        }
        $phone->update();
        return response(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if(env('APP_ENVIROMENT') != 'production'){
            SMSPhoneNumber::destroy($id);
            auth()->user()->tenant->updatePhoneNumberFee();
            $response = [
                'status' => 200,
                'response' => [

                ]
            ];
        }
        else{
            $phone = SMSPhoneNumber::findOrFail($id);
            $twilio = new TwilioAPI();
            $response = $twilio->releasePhoneNumber(array_get($phone, 'sid'));
            if(array_get($response, 'status') == 200){
                SMSPhoneNumber::destroy($id);
                auth()->user()->tenant->updatePhoneNumberFee();
            }
        }
        
        return response()->json($response);
    }
}
