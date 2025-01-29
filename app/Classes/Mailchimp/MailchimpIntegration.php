<?php

namespace App\Classes\Mailchimp;

use GuzzleHttp\Client;

/**
 * Description of MailchimpIntegration
 *
 * @author josemiguel
 */
class MailchimpIntegration {
    CONST INIT_URL = 'https://[:data-center:].api.mailchimp.com/3.0/';
    private $baseUrl = '';
    private $uri = '';
    private $token = null;
    private $client = null;
    private $headers = [
        'Authorization' => 'Bearer [:token:]',
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type' => 'application/json',
    ];

    public function __construct() {
        $this->client = new Client();
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    public function setUri($uri) {
        $this->uri = $this->baseUrl.$uri;
    }

    public function getToken() {
        return $this->token;
    }
    
    public function setToken($token) {
        $this->token = $token;
        $auth = str_replace('[:token:]', $token, array_get($this->headers, 'Authorization'));
        array_set($this->headers, 'Authorization', $auth);
        
        $dc = last(explode('-', $token));
        $this->baseUrl = str_replace('[:data-center:]', $dc, self::INIT_URL);
    }

    public function getClient() {
        return $this->client;
    }
    
    public function setClient($client) {
        $this->client = $client;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    protected function request($method, $uri = null, $json = null) {
        if(!$uri){
            $uri = $this->uri;
        }
        $options = [ 'headers' => $this->headers, 'body' => $json ];
        try {
            $response = $this->client->request($method, $uri, $options);
            return $response;
            /*
            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), $as_array);
                return $result;
            }
            if( $response->getStatusCode() === 204 ){
                $response = new \App\Classes\ApiJsonResponse($response->getStatusCode());
                $response->setMessage(__("Member has been removed from list succesfully"));
                return $response;
            }
             * 
             */
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            return $ex->getResponse()->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            return $ex->getResponse()->getBody()->getContents();
        }
        abort(500);
    }
    
    public function __destruct() {
        unset($this->token);
        unset($this->client);
        unset($this->uri);
        unset($this->headers);
    }

}
