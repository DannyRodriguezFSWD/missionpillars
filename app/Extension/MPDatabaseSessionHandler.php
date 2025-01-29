<?php 
namespace App\Extension;

use Carbon\Carbon;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Arr;

// thanks https://stackoverflow.com/a/24282759/2884623
class MPDatabaseSessionHandler extends DatabaseSessionHandler {

    
    /**
     * Extended to include updated_at, if not exists, created_at, and if available, timezone
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultPayload($data)
    {
        $payload = parent::getDefaultPayload($data);
        
        // Now add any values for sessions columns
        $timezone = session()->get('timezone');
        if ($timezone) Arr::set($payload, 'timezone', $timezone);
        if (! $this->exists) Arr::set($payload, 'created_at', Carbon::now());
        Arr::set($payload, 'updated_at', Carbon::now());
        

        return $payload;
    }
    
    /** override others if  needed **/
    
    /*
    public function read($sessionId)
    {
        // Reading the session
    }

    public function write($sessionId, $data)
    {
        // Writing the sesssion
    }
    
    public function destroy($sessionId)
    {
        // Destryoing the Session
    }

    public function gc($lifetime)
    {
        // Cleaning up expired sessions
    }
    */

}
