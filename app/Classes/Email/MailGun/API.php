<?php

namespace App\Classes\Email\Mailgun;

use GuzzleHttp\Client;

/**
 * Description of Status
 *
 * @author josemiguel
 */
class API {

    private $base = 'https://api.mailgun.net/v3/::DOMAIN_NAME::/';
    private $url;
    private $client;
    private $domain;
    private $secret;

    public function __construct($domain = null, $secret = null) {
        $this->client = new Client();
        $this->domain = $domain;
        $this->secret = $secret;
        
        if(is_null($domain)){
            $this->url = str_replace('::DOMAIN_NAME::', env('MAILGUN_DOMAIN'), $this->base);
            $this->secret = env('MAILGUN_SECRET');
        }
        else{
            $this->url = str_replace('::DOMAIN_NAME::', $domain, $this->base);
        }
    }

    /**
     * Gets email status from API
     */
    public function status($page = null, $params = []) {
        if(!is_null($page)){
            $url = $page;
        }
        else{
            $params_encoded = http_build_query($params);
            $url = $this->url.'events?'.$params_encoded;
        }
        
        $response = $this->client->get($url, [
            'auth' => ['api', $this->secret]
        ]);
        
        if($response->getStatusCode() === 200){
            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        }
        return null;
    }

    public function __destruct() {
        
    }

}
