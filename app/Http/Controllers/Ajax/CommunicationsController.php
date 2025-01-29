<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Communication;
use App\Models\StatementTemplate;

class CommunicationsController extends Controller
{
    /**
     * Obtains a summary of emails to be sent by specified communication
     * @param  [integer] $id    id of Communication 
     * @return [Response]
     */
    public function getEmailSummary($id) {
        ini_set('memory_limit', '1024M');
        
        $communication = Communication::with(['lists.inTags','lists.notInTags'])->findOrFail($id);
        
        return response()->json($communication->emailSummary(true));
    }
    
    /**
     * Obtains a summary of emails to be sent by specified communication
     * @param  [integer] $id    id of Communication 
     * @return [Response]
     */
    public function getPrintSummary($id) {
        ini_set('memory_limit', '1024M');
        
        $communication = Communication::findOrFail($id);
        $communication->load('lists.inTags','lists.notInTags');
        
        return response()->json($communication->printSummary(true));
    }
    
    /**
     * Queues specified communication as emails
     * @param  [integer] $id    id of Communication 
     * @return [Response]
     */
    public function sendEmail($id, Request $request) {
        ini_set('memory_limit', '1024M');
        
        $communication = Communication::findOrFail($id);
        
        $timeUTC = array_get($request, 'time_scheduled') ? setUTCDateTime(array_get($request, 'time_scheduled')) : date('Y-m-d H:i:s');
        $communication->time_scheduled = $timeUTC;
        $communication->update();
        
        $communication->sendEmail();
        return response()->json(true);
    }
    
    /**
     * Tracks contacts that have been printed  specified communication as email
     * @param  [integer] $id    id of Communication 
     * @return [Response]
     */
    public function trackPrintedContacts($id) {
        $communication = Communication::findOrFail($id);
        $communication->trackPrintedContacts();
    }
}
