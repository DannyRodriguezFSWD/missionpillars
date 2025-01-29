<?php

namespace App\Classes\Tiny;

use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;

class Tiny 
{
    const TINY_DRIVE_ROOT = 'https://claims.tiny.cloud/drive/root';
    const PRIVATE_KEY_PATH = 'tiny/private.pkcs1.pem.txt';

    protected $headers = [];
    protected $privateKey;
    protected $payload = [];

    public function __construct() 
    {
        $this->setHeaders();
        $this->loadPrivateKey();
        $this->loadPayload();
    }
    
    protected function setHeaders() 
    {
        $this->headers['Access-Control-Allow-Origin'] = '*';
        $this->headers['Access-Control-Allow-Headers'] = 'Origin, X-Requested-With, Content-Type, Accept';
        $this->headers['Content-Type'] = 'application/json';
    }
    
    protected function loadPrivateKey()
    {
        $this->privateKey = Storage::disk('keys')->get(self::PRIVATE_KEY_PATH);
    }
    
    protected function loadPayload()
    {
        $tenant = auth()->user()->tenant;
        
        $this->payload['sub'] = 'continuetogive_'.config('app.env').'_'.$tenant->id;
        $this->payload['name'] = $tenant->organization;
        $this->payload[self::TINY_DRIVE_ROOT] = '/'.$this->payload['sub'];
        $this->payload['exp'] = time() + 60 * 10; // 10 minute expiration
    }

    public function getToken()
    {
        $token = JWT::encode($this->payload, $this->privateKey, 'RS256');
        $response = json_encode(['token' => $token]);
        
        return response($response)->withHeaders($this->headers);
    }
}
