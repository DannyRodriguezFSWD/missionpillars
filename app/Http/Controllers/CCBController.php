<?php

namespace App\Http\Controllers;

use App\Classes\CCB\CCB;
use App\Models\Integration;

class CCBController 
{
    public function index() 
    {
        $integration = Integration::where('service', 'CCB')->firstOrFail();
        return view('integration.apps.ccb.index')->with(compact('integration'));
    }
    
    public function sync($id)
    {
        $integration = Integration::findOrFail($id);
        
        $ccb = new CCB(
            array_get($integration, 'url'), 
            array_get($integration, 'username'), 
            array_get($integration, 'password'), 
            array_get($integration, 'custom_fields')
        );
        
        $ccb->sync(array_get($integration, 'date_last_sync'));
        
        array_set($integration, 'date_last_sync', date('Y-m-d H:i:s'));
        $integration->update();
    }
}
