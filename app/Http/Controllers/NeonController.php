<?php

namespace App\Http\Controllers;

use App\Classes\Neon\Neon;
use App\Models\Integration;

class NeonController 
{
    public function index() 
    {
        $integration = Integration::where('service', 'neon')->firstOrFail();
        return view('integration.apps.neon.index')->with(compact('integration'));
    }
    
    public function sync($id)
    {
        $integration = Integration::findOrFail($id);
        
        $neon = new Neon(array_get($integration, 'username'), array_get($integration, 'password'));
        
        $neon->sync(array_get($integration, 'date_last_sync'));
        
        array_set($integration, 'date_last_sync', date('Y-m-d H:i:s'));
        
        $integration->update();
        
        return response()->json(['success' => true]);
    }
}
