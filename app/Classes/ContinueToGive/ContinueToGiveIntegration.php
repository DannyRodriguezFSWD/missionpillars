<?php

namespace App\Classes\ContinueToGive;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Constants;

/**
 * Description of ContinueToGiveIntegration
 *
 * @author josemiguel
 */
class ContinueToGiveIntegration {

    private $uri = '';
    private $token;
    private $client = null;

    public function __construct($token = null) {
        $this->token = $token;
        $this->uri = env('API_URL');
        $this->client = new Client(['verify' => false]);
    }

    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
        $this->uri = self::INIT_URL . $token . '&';
    }

    public function getClient() {
        return $this->client;
    }

    public function setClient($client) {
        $this->client = $client;
    }

    /**
     * used after GET url setup
     * @param String $method
     * @param String $uri
     * @param Array $params
     * @param Json $json
     * @return Result | Exception
     */
    protected function get($method, $uri, $params = [], $json = null) {
        $options = http_build_query($params);
        $uri .= $options;
        try {
            $response = $this->client->request($method, $uri);
            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                return $result;
            }
            else if ($response->getStatusCode() === 204) {
                return ['status_code' => $response->getStatusCode()];
            }
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            return json_decode($ex->getResponse()->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            return json_decode($ex->getResponse()->getBody()->getContents(), true);
        }
        abort(500);
    }
    
    public function getTransactions($params = []) {
        $uri = $this->uri . 'transactions?token=' . $this->token .'&';
        $uri .= http_build_query($params);
        
        $response = $this->get('GET', $uri);
        return $response;
    }

    public function getSingleSignOnData($id) {
        $uri = $this->uri . 'singlesignon?one_time_token=' . $id;
        $response = $this->get('GET', $uri);
        return $response;
    }


    /**
     * Specific POST endpoint data
     * @param String $c2gToken
     * @param Array $data
     * @return bool|Exception
     */
    public function returnToken($c2gToken = null, $data = null) {
        $uri = $this->uri . 'accountingintegration';
        $headers = [
            'token' => $c2gToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $options = [
            'headers' => $headers,
            'form_params' => $data
        ];
        
        try {
            $response = $this->client->request('POST', $uri, $options);
            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents(), true);
                return $result;
            }
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            return json_decode($ex->getResponse()->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            return json_decode($ex->getResponse()->getBody()->getContents(), true);
        }
    }

    public function __destruct() {
        unset($this->token);
        unset($this->uri);
        unset($this->client);
    }

}
