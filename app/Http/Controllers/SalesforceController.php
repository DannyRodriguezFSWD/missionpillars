<?php

namespace App\Http\Controllers;

use App\Classes\Salesforce\Salesforce;
use App\Models\Integration;

class SalesforceController 
{
    public function index() 
    {
        $integration = Integration::where('service', 'salesforce')->firstOrFail();
        return view('integration.apps.salesforce.index')->with(compact('integration'));
    }
    
    public function sync($id)
    {
        $integration = Integration::findOrFail($id);
        
        $salesforce = new Salesforce(array_get($integration, 'api_key'), array_get($integration, 'url'));
        
        $salesforce->sync(array_get($integration, 'date_last_sync'));
        
        array_set($integration, 'date_last_sync', date('Y-m-d H:i:s'));
        
        $integration->update();
        
        return response()->json(['success' => true]);
    }
}
