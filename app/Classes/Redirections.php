<?php

namespace App\Classes;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\PledgeForm;
use App\Models\Group;
use App\Models\Form;

/**
 * This helper class replace the way sign up flow used to work
 * with all params in url
 *
 * @author josemiguel
 */
class Redirections {

    /**
     * Unset the values stored in session
     * @param type $name
     */
    public static function destroy($name = 'redirect_url') {
        request()->session()->forget($name);
        request()->session()->forget('form_filled_from_url');
    }

    /**
     * gets the session value
     * @param type $name
     * @return type
     */
    public static function get($name = 'redirect_url') {
        $url = session($name);
        return $url;
    }

    /**
     * Stores the session value
     * @param type $request
     */
    public static function store($request, $replaceCurrentRedirectUrl = false) {
        $start_url = array_get($request, 'start_url');
        if (is_null(session('redirect_url')) || $replaceCurrentRedirectUrl) {
            $request->session()->put('redirect_url', $start_url);
        }
        
        if(is_null(session('form_filled_from_url')) && strpos($start_url, 'calendar') !== false){
            $request->session()->put('form_filled_from_url', array_get($request, 'next_url'));
        }
    }

    /**
     * gets modell entity based  in url params
     * @param type $request
     * @return type
     */
    public static function getEntityFromSession($request) {
        if ($request->session()->exists('form_filled_from_url')) {
            $redirect = session('form_filled_from_url');
        }
        else{
            $redirect = session('redirect_url');
        }
        
        $entity = null;
        $pieces = array_reverse(explode('/', $redirect));
        
        if(empty($pieces) || count($pieces) < 2){
            return null;
        }
        
        if (strpos($redirect, 'events') !== false && strpos($redirect, 'checkin') === false) {//come from public event
            $entity = CalendarEventTemplateSplit::where('uuid', $pieces[1])->first();
        }
        else if (strpos($redirect, 'events') !== false && strpos($redirect, 'checkin') !== false){//comes from checkinscreen
            $entity = CalendarEventTemplateSplit::findOrFail($pieces[1]);
        } else if (strpos($redirect, 'join') !== false) {
            $entity = Group::where('uuid', $pieces[0])->first();
        } else if (strpos($redirect, 'pledges') !== false) {
            $entity = PledgeForm::where('uuid', $pieces[2])->first();
        } else {
            $entity = Form::where('uuid', $pieces[1])->first();
        }
        
        return $entity;
    }

}
