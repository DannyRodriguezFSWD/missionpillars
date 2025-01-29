<?php

namespace App\Classes\Stripo;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;

class StripoAPI 
{
    CONST END_POINT = 'https://plugins.stripo.email/api/v1/auth';

    private $client;
    private $headers;
    
    public function __construct() 
    {
        $this->client = new Client();
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        
        $this->setHeaders($headers);
    }
    
    protected function setHeaders($headers) 
    {
        $this->headers = $headers;
    }
    
    public function getToken()
    {
        $body = [
            'pluginId' => env('STRIPO_PLUGIN_ID'),
            'secretKey' => env('STRIPO_SECRET_KEY'),
            'role' => 'ADMIN'
        ];
        
        $options = [
            'headers' => $this->headers, 
            'body' => json_encode($body)
        ];
        
        try {
            $response = $this->client->request('POST', static::END_POINT, $options);
            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $log = [
                'event' => 'stripo_api_call',
                'url' => $uri,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => $json,
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return $ex;
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            $log = [
                'event' => 'stripo_api_call',
                'url' => $uri,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => $json,
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return $ex;
        } catch (\GuzzleHttp\Exception\ConnectException $ex) {
            $log = [
                'event' => 'stripo_api_call',
                'url' => $uri,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'request' => $json,
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return $ex;
        }
        abort(500);
    }
}
