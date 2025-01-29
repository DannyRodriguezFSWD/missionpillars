<?php

namespace App\Classes\Salesforce;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;

class SalesforceAPI 
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct($apiKey, $apiUrl, $version = 'v57.0') 
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
        $this->apiUrl = 'https://'.$apiUrl.'/services/data/'.$version.'/query?q=';
        $this->setHeaders();
    }
    
    private function setHeaders() 
    {
        $this->headers = [
            'Authorization' => 'Bearer '. $this->apiKey
        ];
    }
    
    private function handleRequest($query, $data, $function, $method = 'GET', $timeout = 20)
    {
        $url = $this->apiUrl.urlencode($query);
        
        try {
            $response = $this->client->request($method, $url, ['headers' => $this->headers]);
            $output = $response->getBody()->getContents();
            return $this->parseOutput($output);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $log = [
                'event' => 'bloomerang_api_call',
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
                'event' => 'bloomerang_api_call',
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
                'event' => 'bloomerang_api_call',
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
    
    private function parseOutput($output)
    {
        return json_decode($output, true);
    }
    
    private function getObject($object, $function, $start = null, $take = 200, $lastModified = null, $where = null)
    {
        if (!$where) {
            $where = "WHERE Id != null ";
        }
        
        if ($start) {
            $where.= " AND Id > '$start' ";
        }
        
        if ($lastModified) {
            $where.= " AND LastModifiedDate >= $lastModified ";
        }
        
        if ($take === 0) {
            $query = "SELECT Id FROM $object $where ORDER BY Id";
        } else {
            $query = "SELECT FIELDS(ALL) FROM $object $where ORDER BY Id LIMIT $take";
        }
        
        return $this->handleRequest($query, null, $function);
    }
    
    private function getObjectById($object, $id, $function)
    {
        $query = "SELECT FIELDS(ALL) FROM $object WHERE Id = '$id'";
        
        $resposne = $this->handleRequest($query, null, $function);
        
        return array_get($resposne, 'records.0');
    }
    
    public function getHouseholds($start = null, $take = 200, $lastModified = null)
    {
        $where = "where Type = 'Household'";
        
        return $this->getObject('ACCOUNT', __FUNCTION__, $start, $take, $lastModified, $where);
    }
    
    public function getContacts($start = null, $take = 200, $lastModified = null)
    {
        return $this->getObject('CONTACT', __FUNCTION__, $start, $take, $lastModified);
    }
    
    public function getAccounts($start = null, $take = 200, $lastModified = null)
    {
        $where = "where Type != 'Household'";
        
        return $this->getObject('ACCOUNT', __FUNCTION__, $start, $take, $lastModified, $where);
    }
    
    public function getAccountById($id)
    {
        return $this->getObjectById('ACCOUNT', $id, __FUNCTION__);
    }
    
    public function getAffiliations($start = null, $take = 200, $lastModified = null)
    {
        return $this->getObject('npe5__Affiliation__c', __FUNCTION__, $start, $take, $lastModified);
    }
    
    public function getCampaigns($start = null, $take = 200, $lastModified = null)
    {
        return $this->getObject('CAMPAIGN', __FUNCTION__, $start, $take, $lastModified);
    }
    
    public function getOpportunities($start = null, $take = 200, $lastModified = null)
    {
        return $this->getObject('OPPORTUNITY', __FUNCTION__, $start, $take, $lastModified);
    }
}
