<?php

namespace App\Classes;

/**
 * Description of ApiUnauthorizedToken
 *
 * @author josemiguel
 */
class ApiJsonResponse {
    protected $status;
    private $message;
    private $data;
    private $statusMessages = [
        '200' => 'OK',
        '204' => 'No Content',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '500' => 'Internal Server Error'
    ];

    public function __construct($status = 401, $data = null) {
        $this->status = $status;
        $this->message = $this->statusMessages[$status];
        $this->data = $data;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getData() {
        return $this->data;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setData($data) {
        $this->data = $data;
    }
    
    public function toJson() {
        $properties = get_object_vars($this);
        unset($properties['statusMessages']);
        return response()->json($properties, $this->status);
    }
    
}
