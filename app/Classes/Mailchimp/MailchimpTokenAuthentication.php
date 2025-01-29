<?php

namespace App\Classes\Mailchimp;

use App\Classes\Mailchimp\MailchimpIntegration;
/**
 * Description of BasicAuthentication
 *
 * @author josemiguel
 */
class MailchimpTokenAuthentication extends MailchimpIntegration {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function __destruct() {
        parent::__destruct();
    }
    
    public function getAccount() {
        return $this->request('GET');
    }
    
    public function getLists() {
        $this->setUri('lists');
        return $this->request('GET', $this->getUri());
    }
    
    public function getList($id) {
        $this->setUri("lists/$id");
        return $this->request('GET', $this->getUri());
    }
    
    public function getMembers($id, $offset = 0) {
        $this->setUri("lists/$id/members?offset=$offset");
        return $this->request('GET', $this->getUri());
    }
    
    public function subscribeMember($id, $data) {
        $this->setUri("lists/$id/members/". md5(array_get($data, 'email_address')));
        return $this->request('PUT', $this->getUri(), json_encode($data));
    }
    
    public function unsubscribeMember($id, $member) {
        $this->setUri("lists/$id/members/$member");
        return $this->request('DELETE', $this->getUri());
    }
    
    public function subscribeMembers($data) {
        $this->setUri("batches");
        return $this->request('POST', $this->getUri(), json_encode($data));
    }
    
    public function setList($data) {
        $this->setUri("lists");
        return $this->request('POST', $this->getUri(), json_encode($data));
    }
    
    public function deleteList($id) {
        $this->setUri("lists/$id");
        return $this->request('DELETE', $this->getUri());
    }
    
}
