<?php

namespace App\Http\Controllers;

use App\Classes\Bloomerang\Bloomerang;
use App\Models\Integration;

class BloomerangController 
{
    public function index() 
    {
        $integration = Integration::where('service', 'bloomerang')->firstOrFail();
        return view('integration.apps.bloomerang.index')->with(compact('integration'));
    }
    
    public function sync($id)
    {
        $integration = Integration::findOrFail($id);
        
        $bloomerang = new Bloomerang(array_get($integration, 'api_key'));
        
        $bloomerang->sync(array_get($integration, 'date_last_sync'));
        
        array_set($integration, 'date_last_sync', date('Y-m-d H:i:s'));
        
        $integration->update();
        
        return response()->json(['success' => true]);
    }
}
