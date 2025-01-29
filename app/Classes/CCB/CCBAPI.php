<?php

namespace App\Classes\CCB;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;

class CCBAPI 
{
    protected $client;
    protected $apiUrl;
    protected $username;
    protected $password;
    protected $customFields;
    
    public function __construct($apiUrl, $username, $password, $customFields = null) 
    {
        $this->client = new Client();
        $this->apiUrl = 'https://'.$apiUrl.'/api.php?srv=';
        $this->username = $username;
        $this->password = $password;
        $this->customFields = $customFields;
    }
    
    protected function handleRequest($url, $data, $function, $method = 'POST', $timeout = 20)
    {
        try {
            $response = $this->client->request($method, $url, ['auth' => [$this->username, $this->password]]);
            $output = $response->getBody()->getContents();
            
            return $this->parseOutput($output);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $log = [
                'event' => 'ccb_api_call',
                'url' => $url,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode($data),
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return $ex;
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            $log = [
                'event' => 'ccb_api_call',
                'url' => $url,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode($data),
                'response' => $ex->getMessage()
            ];
            MissionPillarsLog::log($log);
            return $ex;
        } catch (\GuzzleHttp\Exception\ConnectException $ex) {
            $log = [
                'event' => 'ccb_api_call',
                'url' => $url,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode($data),
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return $ex;
        }
    }
    
    protected function parseOutput($output)
    {
        $resposne = simplexml_load_string($output);
        return $resposne->response;
    }
    
    public function individualProfiles($date = null, $page = null, $perPage = null)
    {
        $url = $this->apiUrl.'individual_profiles&include_inactive=true';
        
        if ($date) {
            $url.= '&modified_since='.$date;
        }
        
        if ($page) {
            $url.= '&page='.$page;
        }
        
        if ($perPage) {
            $url.= '&per_page='.$perPage;
        }
        
        $response = $this->handleRequest($url, null, __FUNCTION__);
        return $response->individuals;
    }
    
    public function groupProfiles($date = null, $includeParticipants = true)
    {
        $url = $this->apiUrl.'group_profiles';
        
        if (!$includeParticipants) {
            $url.= '&include_participants=false';
        }
        
        if ($date) {
            $url.= '&modified_since='.$date;
        }
        
        $response = $this->handleRequest($url, null, __FUNCTION__);
        return $response->groups;
    }
    
    public function batchProfiles($date = null)
    {
        $url = $this->apiUrl.'batch_profiles';
        
        if (empty($date)) {
            $date = date('Y-m-d', strtotime(date('Y-m-d').' - 2 year'));
        }
        
        if ($date) {
            $url.= '&modified_since='.$date;
        }
        
        $response = $this->handleRequest($url, null, __FUNCTION__);
        return $response->batches;
    }
    
    public function transactionDetailTypeList()
    {
        $url = $this->apiUrl.'transaction_detail_type_list';
        
        $response = $this->handleRequest($url, null, __FUNCTION__);
        return $response->transaction_detail_types;
    }
}
