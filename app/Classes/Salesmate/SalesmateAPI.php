<?php

namespace App\Classes\Salesmate;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;

class SalesmateAPI
{
    protected $client;
    protected $headers;
    protected $apiVersion;
    protected $pipeLines;
    protected $sameWeightStages;
    protected $canAlwaysReturnToStages;
    
    const CATEGORY = 'salesmate';
    const DATE_FORMAT = 'M d, Y h:i A';
    
    public function __construct($apiVersion = 'v4') 
    {
        $this->client = new Client();
        $this->apiVersion = $apiVersion;
        $this->setHeaders();
        $this->loadPipelines();
        $this->loadSameWeightStages();
    }
    
    private function generateApiUrl($module, $append = null)
    {
        $url = env('SALESMATE_API_URL').$module.'/v4';
        
        if ($append) {
            $url.= '/'.$append;
        }
        
        return $url;
    }
    
    protected function setHeaders() 
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'accessToken' => env('SALESMATE_SESSION_TOKEN'),
            'x-linkname' => env('SALESMATE_LINK_NAME')
        ];
    }
    
    /**
     * This ensures we don't add the same header twice
     * 
     * @param string $key
     * @param string $value
     */
    protected function addHeader($key, $value)
    {
        $hasHeader = false;
        
        foreach ($this->headers as $header) {
            if (strpos($header, $key) !== false) {
                $hasHeader = true;
                break;
            }
        }
        
        if (!$hasHeader) {
            $this->headers[] = "$key: $value";
        }
    }
    
    protected function removeHeader($header)
    {
        foreach ($this->headers as $key => $val) {
            if (strpos($val, $header) !== false) {
                unset($this->headers[$key]);
                break;
            }
        }
    }
    
    protected function loadPipelines() 
    {
        $this->pipeLines = collect(config('salesmate.pipelines'));
    }
    
    protected function loadSameWeightStages()
    {
        foreach (config('salesmate.sameWeightStages') as $stages) {
            $temp = [];
            
            foreach ($stages as $stage) {
                $temp[] = config('salesmate.pipelines.'.$stage);
            }
            
            $this->sameWeightStages[] = $temp;
        }
    }
    
    protected function handleRequest($url, $data, $function, $method = 'POST', $timeout = 20)
    {
        if (!env('SALESMATE_ENABLED')) {
            return null;
        }
        
        if (is_null($data)) {
            $this->addHeader('Content-length', 0);
        } else {
            $this->removeHeader('Content-length');
        }
        
        $options = [
            'headers' => $this->headers, 
            'body' => (empty($data) ? null : json_encode($data))
        ];
        
        try {
            $response = $this->client->request($method, $url, $options);
            $output = $response->getBody()->getContents();
            return $this->parseOutput($output);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $log = [
                'event' => 'salesmate_crm_api_call',
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
                'event' => 'salesmate_crm_api_call',
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
                'event' => 'salesmate_crm_api_call',
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
        $response = json_decode($output, true);  
        
        if (array_get($response, 'Status') === 'success') {
            return array_get($response, 'Data');
        } else {
            return null;
        }
    }

    protected function prepareSearchQuery($field, $data, $condition, $moduleName, $fieldsToGet)
    {
        $moduleEx = explode('.', $moduleName);
        
        $query = [
            'displayingFields' => $fieldsToGet,
            'filterQuery' => [
                'group' => [
                    'operator' => 'AND',
                    'rules' => [
                        // this is needed because salesmate api does not work with soft deleted scope
                        [
                            'condition' => 'EQUALS',
                            'field' => [
                                'fieldName' => $moduleName.'.isDeleted'
                            ],
                            'data' => 0,
                            'moduleName' => ucfirst($moduleEx[0])
                        ],
                        [
                            'condition' => $condition,
                            'field' => [
                                'fieldName' => $moduleName.'.'.$field
                            ],
                            'data' => $data,
                            'moduleName' => ucfirst($moduleEx[0])
                        ]
                    ]
                ]
            ]
        ];
        
        return $query;
    }
    
    protected function addConditionToQuery(&$query, $moduleName, $field, $condition, $data)
    {
        $query['filterQuery']['group']['rules'][] = [
            'condition' => $condition,
            'field' => [
                'fieldName' => $moduleName.'.'.$field
            ],
            'data' => $data,
            'moduleName' => ucfirst($moduleName)
        ];
    }
    
    protected function getItem($type, $id, $function = 'getItem')
    {
        if (empty($id)) {
            return null;
        }
        
        $url = $this->generateApiUrl($type, $id);
                
        $response = $this->handleRequest($url, null, $function, 'GET');
        
        if (is_null($response)) {
            return null;
        } else {
            return $response;
        }
    }
    
    public function searchItem($module, $field = null, $data = null, $condition = 'EQUALS', $fieldsToGet = null, $function = 'searchItem', $query = null, $getFirst = true)
    {
        $url = $this->generateApiUrl($module, 'search');
        
        if (empty($query)) {
            $query = $this->prepareSearchQuery($field, $data, $condition, $module, $fieldsToGet);
        }
        
        $response = $this->handleRequest($url, $query, $function);
        
        if (is_null($response)) {
            return null;
        } elseif ($getFirst) {
            return array_get($response, 'data.0');
        } else {
            return array_get($response, 'data');
        }
    }
    
    public function createItem($module, $data, $function = 'createItem')
    {
        $url = $this->generateApiUrl($module);
        
        $response = $this->handleRequest($url, $data, $function);
        
        return $response;
    }
    
    public function updateItem($module, $item, $data, $function = 'updateItem')
    {
        if (empty($item)) {
            return false;
        }
        
        foreach ($data as $key => $val) {
            if (!is_object(array_get($item, $key)) && array_get($item, $key) == array_get($data, $key)) {
                unset($data[$key]);
            }
        }
        
        if (empty($data)) {
            return $item;
        }
        
        $url = $this->generateApiUrl($module, array_get($item, 'id'));
        
        $this->handleRequest($url, $data, $function, 'PUT');
        
        return $this->getItem($module, array_get($item, 'id'));
    }

    public function deleteItem($module, $item, $function = 'deleteItem')
    {
        if (empty($item)) {
            return false;
        }
        
        $url = $this->generateApiUrl($module, array_get($item, 'id'));
        
        $response = $this->handleRequest($url, null, $function, 'DELETE');
        
        return $response;
    }
    
    public function getContact($id)
    {
        return $this->getItem('contact', $id, __FUNCTION__);
    }
    
    public function searchContact($field = null, $data = null, $condition = 'EQUALS', $query = null, $fieldsToGet = null) 
    {
        if (empty($fieldsToGet)) {
            $fieldsToGet = ['contact.id', 'contact.firstName', 'contact.lastName', 'contact.owner.id', 'contact.company.id', 'contact.company.name', 'contact.email', 'contact.'.config('salesmate.customFields.contacts.ctgid')];
        }
        
        return $this->searchItem('contact', $field, $data, $condition, $fieldsToGet, __FUNCTION__, $query);
    }
    
    public function searchContactQuery($data)
    {
        $rules = [];
        
        if (array_get($data, 'email')) {
            $rules[] = [
                'condition' => 'EQUALS',
                'field' => [
                    'fieldName' => 'contact.email'
                ],
                'data' => array_get($data, 'email'),
                'moduleName' => 'Contact'
            ];
        }
        
        if (array_get($data, 'pageid')) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'contact.'.config('salesmate.customFields.contacts.ctgid')
                ],
                'data' => array_get($data, 'pageid'),
                'moduleName' => 'Contact'
            ];
        }
        
        if (array_get($data, config('salesmate.customFields.contacts.mpid'))) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'contact.'.config('salesmate.customFields.contacts.mpid')
                ],
                'data' => array_get($data, config('salesmate.customFields.contacts.mpid')),
                'moduleName' => 'Contact'
            ];
        }        
        
        if (array_get($data, 'ctg_id')) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'contact.'.config('salesmate.customFields.contacts.ctgid')
                ],
                'data' => array_get($data, 'ctg_id'),
                'moduleName' => 'Contact'
            ];
        } 
        
        $query = [
            'displayingFields' => ['contact.id', 'contact.firstName', 'contact.lastName', 'contact.owner.id', 'contact.company.id', 'contact.company.name', 'contact.email', 'contact.mobile', 'contact.'.config('salesmate.customFields.contacts.mpid')],
            'filterQuery' => [
                'group' => [
                    'operator' => 'AND',
                    'rules' => [
                        // this is needed because salesmate api does not work with soft deleted scope
                        [
                            'condition' => 'EQUALS',
                            'field' => [
                                'fieldName' => 'contact.isDeleted'
                            ],
                            'data' => 0,
                            'moduleName' => 'Contact'
                        ],
                        [
                            'group' => [
                                'operator' => 'OR',
                                'rules' => $rules,
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $query;
    }
    
    public function createContact($data)
    {
        return $this->createItem('contact', $data, __FUNCTION__);
    }
    
    public function findOrCreateContact($rawData)
    {
        $data = [
            'firstName' => array_get($rawData, 'name'),
            'lastName' => array_get($rawData, 'last_name'),
            'mobile' => array_get($rawData, 'tenant.phone'),
            'email' => array_get($rawData, 'email'),
            config('salesmate.customFields.contacts.mpid') => array_get($rawData, 'id')
        ];
        
        $contact = $this->searchContact(null, null, null, $this->searchContactQuery($data));
        
        if (empty($contact)) {
            $data['owner'] = config('salesmate.defaultOwnerId');
            $contactId = $this->createContact($data);
            $contact = $this->getContact(array_get($contactId, 'id'));
        } else {
            $this->updateContact($contact, $data);
        }
        
        return $contact;
    }
    
    public function updateContact($contact, $data) 
    {
        return $this->updateItem('contact', $contact, $data, __FUNCTION__);
    }
    
    public function deleteContact($contact)
    {
        return $this->deleteItem('contact', $contact, __FUNCTION__);
    }
    
    /**
     * This is needed because search returns uppercase Owner and create returns lower case owner :(
     * 
     * @param type $contact
     * @return type
     */
    public function getContactOwner($contact)
    {
        return array_get($contact, 'Owner.id', array_get($contact, 'owner.id'));
    }
    
    public function getContactCompany($contact)
    {
        return array_get($contact, 'Company.id', array_get($contact, 'company.id'));
    }
    
    public function getContactCompanyName($contact)
    {
        return array_get($contact, 'Company.name', array_get($contact, 'company.name'));
    }
    
    public function getCompany($id)
    {
        return $this->getItem('company', $id, __FUNCTION__);
    }
    
    public function searchCompany($field = null, $data = null, $condition = 'EQUALS', $query = null, $fieldsToGet = null)
    {
        if (empty($fieldsToGet)) {
            $fieldsToGet = ['company.id', 'company.'.config('salesmate.customFields.companies.ctgid')];
        }
        
        return $this->searchItem('company', $field, $data, $condition, $fieldsToGet, __FUNCTION__, $query);
    }
    
    public function searchCompanyQuery($data)
    {
        $rules = [];
        
        if (array_get($data, config('salesmate.customFields.companies.email'))) {
            $rules[] = [
                'condition' => 'EQUALS',
                'field' => [
                    'fieldName' => 'company.'.config('salesmate.customFields.companies.email')
                ],
                'data' => array_get($data, config('salesmate.customFields.companies.email')),
                'moduleName' => 'Company'
            ];
        }
        
        if (array_get($data, 'pageid')) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'company.'.config('salesmate.customFields.companies.ctgid')
                ],
                'data' => array_get($data, 'pageid'),
                'moduleName' => 'Company'
            ];
        }
        
        if (array_get($data, config('salesmate.customFields.companies.mpid'))) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'company.'.config('salesmate.customFields.companies.mpid')
                ],
                'data' => array_get($data, config('salesmate.customFields.companies.mpid')),
                'moduleName' => 'Company'
            ];
        }        
        
        if (array_get($data, 'ctg_id')) {
            $rules[] = [
                'condition' => '=',
                'field' => [
                    'fieldName' => 'company.'.config('salesmate.customFields.companies.ctgid')
                ],
                'data' => array_get($data, 'ctg_id'),
                'moduleName' => 'Company'
            ];
        } 
        
        $query = [
            'displayingFields' => ['company.id', 'company.name', 'company.phone', 'company.'.config('salesmate.customFields.companies.email'), 'company.'.config('salesmate.customFields.companies.mpid')],
            'filterQuery' => [
                'group' => [
                    'operator' => 'AND',
                    'rules' => [
                        // this is needed because salesmate api does not work with soft deleted scope
                        [
                            'condition' => 'EQUALS',
                            'field' => [
                                'fieldName' => 'company.isDeleted'
                            ],
                            'data' => 0,
                            'moduleName' => 'Company'
                        ],
                        [
                            'group' => [
                                'operator' => 'OR',
                                'rules' => $rules,
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $query;
    }
    
    public function createCompany($data)
    {
        return $this->createItem('company', $data, __FUNCTION__);
    }
    
    public function findOrCreateCompany($rawData, $contact)
    {
        $data = [
            'name' => array_get($rawData, 'tenant.organization'),
            'phone' => array_get($rawData, 'tenant.phone'),
            config('salesmate.customFields.companies.email') => array_get($rawData, 'email'),
            config('salesmate.customFields.companies.mpid') => array_get($rawData, 'tenant.id')
        ];
        
        $company = $this->searchCompany(null, null, null, $this->searchCompanyQuery($data));
        
        if (empty($company)) {
            $data['owner'] = empty($this->getContactOwner($contact)) ? config('salesmate.defaultOwnerId') : $this->getContactOwner($contact);
            $companyId = $this->createCompany($data);
            $company = $this->getCompany(array_get($companyId, 'id'));
        } else {
            $this->updateCompany($company, $data);
        }
        
        return $company;
    }
    
    public function updateCompany($company, $data)
    {
        return $this->updateItem('company', $company, $data, __FUNCTION__);
    }
    
    public function deleteCompany($company)
    {
        return $this->deleteItem('company', $company, __FUNCTION__);
    }
    
    public function setContactInCompany($contact, $company)
    {
        if ($this->getContactCompany($contact) != array_get($company, 'id')) {
            $this->updateContact($contact, ['company' => array_get($company, 'id')]);
        }
    }
    
    public function searchDeal($field = null, $data = null, $pipeline = null, $title = null, $condition = 'EQUALS', $query = null, $fieldsToGet = ['deal.id', 'deal.primaryContact.id', 'deal.pipeline', 'deal.stage', 'deal.title', 'deal.status'])
    {
        if (empty($pipeline)) {
            $pipeline = config('salesmate.pipelines.mp.name');
        }
        
        $dealQuery = $this->prepareSearchQuery($field, $data, $condition, 'deal.primaryContact', $fieldsToGet);
        $this->addConditionToQuery($dealQuery, 'deal', 'isDeleted', 'EQUALS', 0);
        $this->addConditionToQuery($dealQuery, 'deal', 'pipeline', 'EQUALS', $pipeline);
        
        if (!empty($title)) {
            $this->addConditionToQuery($dealQuery, 'deal', 'title', 'CONTAINS', $title);
        }
        
        return $this->searchItem('deal', null, null, null, null, __FUNCTION__, $dealQuery);
    }
    
    public function searchDealByUserId($userId, $pipeline = null, $title = null) 
    {
        if (empty($pipeline)) {
            $pipeline = config('salesmate.pipelines.mp.name');
        }
        
        return $this->searchDeal(config('salesmate.customFields.contacts.mpid'), $userId, $pipeline, $title, '=');
    }
    
    public function searchDealByEmail($email, $pipeline = null, $title = null) 
    {
        if (empty($pipeline)) {
            $pipeline = config('salesmate.pipelines.mp.name');
        }
        
        return $this->searchDeal('email', $email, $pipeline, $title);
    }
    
    public function getDeal($id)
    {
        return $this->getItem('deal', $id, __FUNCTION__);
    }
    
    public function createDeal($data)
    {
        return $this->createItem('deal', $data, __FUNCTION__);
    }
    
    private function makeDealTitle($title, $contact) 
    {
        $newTitle = $title;
        
        $companyName = $this->getContactCompanyName($contact);
        
        if (!empty($companyName)) {
            $newTitle.= ' - '.$companyName;
        }
        
        $newTitle.= ' - '.array_get($contact, 'firstName').' '.array_get($contact, 'lastName');
        
        return $newTitle;
    }
    
    public function createDealForContact($contact, $stage, $pipeline = null, $status = 'Open', $title = null)
    {
        if (empty($contact)) {
            return null;
        }
        
        if (empty($pipeline)) {
            $pipeline = config('salesmate.pipelines.mp.name');
        }
        
        if (empty($title)) {
            $title = $pipeline;
        }
        
        $data = [
            'primaryContact' => array_get($contact, 'id'),
            'title' => $this->makeDealTitle($title, $contact),
            'pipeline' => $pipeline,
            'owner' => $this->getContactOwner($contact),
            'status' => $status,
            'stage' => $stage
        ];
        
        $companyId = $this->getContactCompany($contact);
        
        if (!empty($companyId)) {
            $data['primaryCompany'] = $companyId;
        }
        
        $deal = $this->createDeal($data);
        
        return $this->getDeal(array_get($deal, 'id'));
    }
    
    public function findOrCreateDeal($userId, $contact, $stage, $pipeline = null, $status = 'Open', $title = null)
    {
        if (empty($contact)) {
            return null;
        }
        
        if (empty($pipeline)) {
            $pipeline = config('salesmate.pipelines.mp.name');
        }
        
        $deal = $this->searchDealByUserId($userId, $pipeline, $title);
        
        if (empty($deal)) {
            $deal = $this->searchDealByEmail(array_get($contact, 'email'), $pipeline, $title);
        }
        
        if (empty($deal)) {
            $deal = $this->createDealForContact($contact, $stage, $pipeline, $status, $title);
        } else {
            if (empty($title)) {
                $title = $pipeline;
            }
            
            $deal = $this->updateDeal($deal, ['title' => $this->makeDealTitle($title, $contact)]);
        }
        
        return $deal;
    }
    
    public function updateDeal($deal, $data, $sync = true)
    {
        if (empty($deal)) {
            return false;
        }
        
        if (!empty(array_get($data, 'stage')) && !$this->canUpdateStage($deal, array_get($data, 'stage'))) {
            unset($data['stage']);
        }
        
        $update = $this->updateItem('deal', $deal, $data, __FUNCTION__);
        
        if ($sync && (!empty(array_get($data, 'stage')) || !empty(array_get($data, 'status')))) {
            $this->syncCompanyDeals($update, array_get($data, 'stage'), array_get($data, 'status'));
        }
        
        return $update;
    }
    
    protected function syncCompanyDeals($deal, $stage, $status)
    {
        $updateData = [];
            
        if (!empty($stage)) {
            $updateData['stage'] = $stage;
        }

        if (!empty($status)) {
            $updateData['status'] = $status;
        }
        
        if (!empty($updateData)) {
            $contact = $this->getContact($this->getDealContact($deal));
            
            if (empty($contact)) {
                return false;
            }
            
            $company = $this->getCompany($this->getContactCompany($contact));
            
            if (empty($company)) {
                return false;
            }
            
            $companyDeals = $this->findAllCompanyDeals(array_get($company, config('salesmate.customFields.companies.ctgid')), array_get($deal, 'pipeline'), $this->getDealTitleShort($deal));

            if (!empty($stage) && !empty($companyDeals)) {
                foreach ($companyDeals as $companyDeal) {
                    if (!$this->canUpdateStage($companyDeal, $stage)) {
                        array_set($updateData, 'stage', array_get($companyDeal, 'stage'));
                        
                        if (empty($status)) {
                            array_set($updateData, 'status', array_get($companyDeal, 'status'));
                        }
                    }
                }
            }
            
            if (!empty($companyDeals)) {
                foreach ($companyDeals as $companyDeal) {
                    $this->updateDeal($companyDeal, $updateData, false);
                }
            }
        }
    }
    
    public function findAllCompanyDeals($ctgId, $pipeline = null, $title = null)
    {
        if (empty($ctgId)) {
            return null;
        }
        
        $dealQuery = $this->prepareSearchQuery(config('salesmate.customFields.companies.ctgid'), $ctgId, '=', 'deal.primaryCompany', ['deal.id', 'deal.primaryCompany.id', 'deal.primaryContact.id', 'deal.pipeline', 'deal.stage', 'deal.title', 'deal.status']);
        $this->addConditionToQuery($dealQuery, 'deal.primaryContact', 'isDeleted', 'EQUALS', 0);
        $this->addConditionToQuery($dealQuery, 'deal', 'isDeleted', 'EQUALS', 0);
        
        if (!empty($pipeline)) {
            $this->addConditionToQuery($dealQuery, 'deal', 'pipeline', 'EQUALS', $pipeline);
        }
        
        if (!empty($title)) {
            $this->addConditionToQuery($dealQuery, 'deal', 'title', 'CONTAINS', $title);
        }
        
        return $this->searchItem('deal', null, null, null, null, __FUNCTION__, $dealQuery, false);
    }
    
    public function getDealContact($deal)
    {
        return array_get($deal, 'PrimaryContact.id', array_get($deal, 'primaryContact.id'));
    }
    
    public function getDealTitleShort($deal)
    {
        $titleEx = explode(' - ', array_get($deal, 'title'));
        return trim($titleEx[0]);
    }
    
    protected function canUpdateStage($deal, $newStage)
    {
        $allStages = collect($this->pipeLines->filter(function ($pipeline) use ($deal) {
            return array_get($pipeline, 'name') === array_get($deal, 'pipeline');
        })->pluck('stages')->first())->values()->toArray();
        
        $oldStage = array_get($deal, 'stage');
        
        if (empty($allStages)) {
            return false;
        }
        
        if (!in_array($newStage, $allStages)) {
            return false;
        }
        
        if (array_search($oldStage, $allStages) < array_search($newStage, $allStages)) {
            return true;
        }
        
        foreach ($this->sameWeightStages as $sameWeightStages) {
            if (in_array($oldStage, $sameWeightStages) && in_array($newStage, $sameWeightStages)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function createActivity($data)
    {
        return $this->createItem('activity', $data, __FUNCTION__);
    }
    
    public function createActivityForContact($contact, $title, $description = null, $dueDate = null, $type = 'Task', $data = [])
    {
        if (empty($contact) || empty($title)) {
            return null;
        }
        
        if (empty($dueDate)) {
            $dueDate = date('Y-m-d H:i');
        }
        
        $data['primaryContact'] = array_get($contact, 'id');
        $data['primaryCompany'] = $this->getContactCompany($contact);
        $data['owner'] = $this->getContactOwner($contact);
        $data['title'] = $title;
        $data['description'] = $description;
        $data['dueDate'] = date(self::DATE_FORMAT, strtotime($dueDate));
        $data['type'] = $type;
        
        return $this->createActivity($data);
    }
}
