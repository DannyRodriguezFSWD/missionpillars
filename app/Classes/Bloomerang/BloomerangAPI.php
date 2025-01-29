<?php

namespace App\Classes\Bloomerang;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;

class BloomerangAPI 
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct($apiKey, $version = 'v2') 
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
        $this->apiUrl = 'https://api.bloomerang.co/'.$version.'/';
        $this->setHeaders();
    }
    
    protected function setHeaders() 
    {
        $this->headers = [
            'X-API-KEY' => $this->apiKey
        ];
    }
    
    protected function handleRequest($url, $data, $function, $method = 'GET', $timeout = 20)
    {
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
    
    protected function parseOutput($output)
    {
        return json_decode($output, true);
    }
    
    public function getConstituents($skip = 0, $take = 50, $lastModified = null)
    {
        $url = $this->apiUrl.'constituents?skip='.$skip.'&take='.$take;
        
        if ($lastModified) {
            $url.= '&lastModified='.$lastModified;
        }
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getFunds($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'funds?skip='.$skip.'&take='.$take;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getCampaigns($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'campaigns?skip='.$skip.'&take='.$take;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getTransactions($skip = 0, $take = 50, $type = null)
    {
        $url = $this->apiUrl.'transactions?skip='.$skip.'&take='.$take.'&orderBy=CreatedDate';
        
        if ($type) {
            $url.= '&type='.$type;
        }
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getRelationships($id)
    {
        $url = $this->apiUrl.'constituent/'.$id.'/relationships';
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getInteractions($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'interactions?skip='.$skip.'&take='.$take.'&orderBy=CreatedDate';
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getSoftCredits($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'softcredits?skip='.$skip.'&take='.$take;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getAttachment($id)
    {
        $url = $this->apiUrl.'attachment/'.$id;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getAppeals($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'appeals?skip='.$skip.'&take='.$take;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getHouseholds($skip = 0, $take = 50, $lastModified = null)
    {
        $url = $this->apiUrl.'households?skip='.$skip.'&take='.$take;
        
        if ($lastModified) {
            $url.= '&lastModified='.$lastModified;
        }
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getNotes($skip = 0, $take = 50)
    {
        $url = $this->apiUrl.'notes?skip='.$skip.'&take='.$take.'&orderBy=CreatedDate';
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getAddresses($skip = 0, $take = 50, $constituent = null)
    {
        $url = $this->apiUrl.'addresses?skip='.$skip.'&take='.$take.'&constituent='.$constituent;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getPhones($skip = 0, $take = 50, $constituent = null)
    {
        $url = $this->apiUrl.'phones?skip='.$skip.'&take='.$take.'&constituent='.$constituent;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function getEmails($skip = 0, $take = 50, $constituent = null)
    {
        $url = $this->apiUrl.'emails?skip='.$skip.'&take='.$take.'&constituent='.$constituent;
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
}
