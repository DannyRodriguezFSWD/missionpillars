<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Settings\Setting;
use App\Models\Settings\SettingValue;
use Ramsey\Uuid\Uuid;
use App\Constants;

class PledgeSettingsController extends Controller {
    const PERMISSION = 'crm-pledges';
    
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
    public function index() {
        $settings = Setting::all();
        $data = [
            'settings' => $settings
        ];
        
        return view('settings.pledges.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }
    
    private function createSettingValue($setting, $value) {
        $settingValue = new SettingValue();
        array_set($settingValue, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
        array_set($settingValue, 'setting_id', array_get($setting, 'id'));
        array_set($settingValue, 'value', $value);
        return $settingValue->save();
    }
    
    private function pledgeEmailReminderSwitch($request) {
        $checked = array_get($request, 'checked');
        $setting = Setting::findOrFail(array_get($request, 'id'));
        $value = array_get($setting, 'value');
        
        $PLEDGE_EMAIL_REMINDER_TEXT_EVERY = array_get($request, 'PLEDGE_EMAIL_REMINDER_TEXT_EVERY');
        if(is_null($PLEDGE_EMAIL_REMINDER_TEXT_EVERY) || $PLEDGE_EMAIL_REMINDER_TEXT_EVERY === ''){
            $PLEDGE_EMAIL_REMINDER_TEXT_EVERY = '0';
        }
        
        $PLEDGE_EMAIL_REMINDER_TEXT_STARTING = array_get($request, 'PLEDGE_EMAIL_REMINDER_TEXT_STARTING');
        if(is_null($PLEDGE_EMAIL_REMINDER_TEXT_STARTING) || $PLEDGE_EMAIL_REMINDER_TEXT_STARTING === '' ){
            $PLEDGE_EMAIL_REMINDER_TEXT_STARTING = '0';
        }
        
        $remindEvery = Setting::where('key', 'PLEDGE_EMAIL_REMINDER_TEXT_EVERY')->first();
        $remindStarting = Setting::where('key', 'PLEDGE_EMAIL_REMINDER_TEXT_STARTING')->first();
        
        if($checked === 'false' ){//User turned off settings so create a new record
            if(is_null($value)){
                $setting = Setting::where('key', 'PLEDGE_EMAIL_REMINDER_SWITCH')->first();
                if( $this->createSettingValue($setting, '0') ){
                    $this->createSettingValue($remindEvery, $PLEDGE_EMAIL_REMINDER_TEXT_EVERY);
                    return $this->createSettingValue($remindStarting, $PLEDGE_EMAIL_REMINDER_TEXT_STARTING);
                }
            }
            else{
                array_set($value, 'value', false);
                return $value->update();
            }
        }
        
        if($checked === 'true' ){//User turned on settings
            if( $PLEDGE_EMAIL_REMINDER_TEXT_EVERY === '5' && $PLEDGE_EMAIL_REMINDER_TEXT_STARTING === '20' && !is_null($value)){
                $remindEveryValue = array_get($remindEvery, 'value');
                $remindStartingValue = array_get($remindStarting, 'value');
                
                $remindEveryValue->delete();
                $remindStartingValue->delete();
                return $value->delete();
            }
            
            if( ($PLEDGE_EMAIL_REMINDER_TEXT_EVERY !== '5' || $PLEDGE_EMAIL_REMINDER_TEXT_STARTING !== '20') && !is_null($value)){
                
                array_set($remindEvery->value, 'value', $PLEDGE_EMAIL_REMINDER_TEXT_EVERY);
                $remindEvery->value->update();
                
                array_set($remindStarting->value, 'value', $PLEDGE_EMAIL_REMINDER_TEXT_STARTING);
                $remindStarting->value->update();
                
                array_set($value, 'value', true);
                return $value->update();
            }
            
            if( ($PLEDGE_EMAIL_REMINDER_TEXT_EVERY !== '5' || $PLEDGE_EMAIL_REMINDER_TEXT_STARTING !== '20') && is_null($value)){
                $this->createSettingValue($remindEvery, $PLEDGE_EMAIL_REMINDER_TEXT_EVERY);
                $this->createSettingValue($remindStarting, $PLEDGE_EMAIL_REMINDER_TEXT_STARTING);
                return $this->createSettingValue($setting, '1');
            }
        }
        
        return "default";
    }
    
    private function pledgeEmailReminderSwitchNotification($request) {
        $setting = Setting::findOrFail(array_get($request, 'id'));
        $value = array_get($setting, 'value');
        $checked = array_get($request, 'checked');
        
        if( $checked === 'false' && is_null($value) ){
            return $this->createSettingValue($setting, '0');
        }
        
        if( $checked === 'true' && !is_null($value) ){
            return $value->delete();
        }
        
        return "default";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $result = false;
        $setting = array_get($request, 'setting');
        
        if( $setting === 'PLEDGE_EMAIL_REMINDER_SWITCH' ){
            $result = $this->pledgeEmailReminderSwitch($request);
        }
        
        $switches = [
            'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT',
            'PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN',
            'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_CONTACT',
            'PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN'
        ];
        
        if( in_array($setting, $switches) ){
            $result = $this->pledgeEmailReminderSwitchNotification($request);
        }
        
        return response()->json($result);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $tenant_settings = SettingValue::whereNotNull('tenant_id')->get()->count();
        while ($tenant_settings <= 0) {
            $system_settings = SettingValue::whereNull('tenant_id')->get();
            foreach ($system_settings as $system) {
                $setting = $system->replicate();
                array_set($setting, 'value', array_get($request, array_get($system, 'key', array_get($system, 'default'))));
                auth()->user()->tenant->settingValues()->save($setting);
            }
            $tenant_settings = SettingValue::whereNotNull('tenant_id')->get()->count();
        }
        $settings = SettingValue::whereNotNull('tenant_id')->get();
        dd("fin", $settings);
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

}
