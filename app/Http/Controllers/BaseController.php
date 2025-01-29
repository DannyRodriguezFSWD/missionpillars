<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller {

    public function __construct(Request $request) {
        //dd($request->getMethod());
        if ($request->getMethod() === 'GET') {
            $timezone_offset_minutes = -360;
            $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes * 60, false);
            date_default_timezone_set($timezone_name);
        }
        else{
            date_default_timezone_set('UTC');
        }
    }

}
