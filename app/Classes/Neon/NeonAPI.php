<?php

namespace App\Classes\Neon;

use App\Classes\MpWrapper\RequestClient as Client;
use App\Classes\MissionPillarsLog;
use Maatwebsite\Excel\Facades\Excel;

class NeonAPI 
{
    protected $client;
    protected $username;
    protected $password;
    protected $apiUrl;
    protected $headers;
    
    protected $accountFields = [
        "Account ID",
        "Account Type",
        "Account Last Modified Date/Time",
        "Address Line 1",
        "Address Line 2",
        "Address Type",
        "City",
        "Company Email",
        "Company ID",
        "Company Name",
        "Contact Type",
        "County",
        "Deceased",
        "DOB Day",
        "DOB Month",
        "DOB Year",
        "Email 1",
        "Email 2",
        "Email 3",
        "Email Opt-Out",
        "First Name",
        "Full Name (F)",
        "Full Street Address (F)",
        "Full Zip Code (F)",
        "Gender",
        "Job Title",
        "Last Name",
        "Middle Name",
        "Note Text",
        "Note Title",
        "Phone 1 Area Code",
        "Phone 1 Full Number (F)",
        "Phone 1 Number",
        "Phone 1 Type",
        "Phone 2 Area Code",
        "Phone 2 Full Number (F)",
        "Phone 2 Number",
        "Phone 2 Type",
        "Phone 3 Area Code",
        "Phone 3 Full Number (F)",
        "Phone 3 Number",
        "Phone 3 Type",
        "Preferred Name",
        "Prefix",
        "State/Province",
        "Zip Code",
        "Zip Code Suffix"
    ];
    
    protected $donationFields = [
        "Account ID",
        "Advantage Amount",
        "Advantage Description",
        "Anonymous Donation",
        "Bank Account Last 4 Digits",
        "Campaign Code",
        "Campaign Goal",
        "Campaign ID",
        "Campaign Name",
        "Cash Amount",
        "Cash Count",
        "Charge ID",
        "Check Number",
        "Commit Amount",
        "Commit Count",
        "Credit Card Expiration Date",
        "Credit Card Last 4 Digits",
        "Credit Card Name",
        "Donation Amount",
        "Donation Batch Number",
        "Donation Created Date",
        "Donation Created Time",
        "Donation Date",
        "Donation ID",
        "Donation Last Modified Time",
        "Donation Solicitation Method",
        "Donation Solicitor",
        "Donation Status",
        "Donation Type",
        "Donor Covered Fees",
        "Donor Note",
        "Eligible Amount",
        "Fund",
        "Fund Code",
        "Fundraiser",
        "Fundraiser Comment",
        "Fundraising Page Title",
        "Non-Deductible Amount",
        "Non-Deductible Amount Description",
        "Parent Campaign ID",
        "Payment Created By",
        "Payment ID",
        "Payment Note",
        "Payment Received Date",
        "Payment Status",
        "Purpose",
        "Purpose Code",
        "Recurring Donation",
        "Tax-Deductible Amount",
        "Total Charge",
        "Transaction Origin Category",
        "Transaction Origin Detail"
    ];

    public function __construct($username, $password, $version = 'v2') 
    {
        $this->client = new Client();
        $this->username = $username;
        $this->password = $password;
        $this->apiUrl = 'https://api.neoncrm.com/'.$version.'/';
        $this->setHeaders();
    }
    
    protected function setHeaders() 
    {
        $this->headers = [
            'Content-Type' => 'application/json'
        ];
    }
    
    protected function handleRequest($url, $data, $function, $method = 'GET', $timeout = 20)
    {
        try {
            $options = [
                'auth' => [$this->username, $this->password],
                'headers' => $this->headers
            ];
            
            if ($data) {
                $options['body'] = json_encode($data);
            }
            
            $response = $this->client->request($method, $url, $options);
            $output = $response->getBody()->getContents();
            
            return $this->parseOutput($output);
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $log = [
                'event' => 'neon_api_call',
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
                'event' => 'neon_api_call',
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
                'event' => 'neon_api_call',
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
    
    /**
     * 
     * @param type $userType INDIVIDUAL or COMPANY
     * @return type
     */
    public function getAccounts($userType = 'INDIVIDUAL')
    {
        $url = $this->apiUrl.'accounts?userType=INDIVIDUAL';
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }
    
    public function searchAccounts($currentPage = 0, $pageSize = 200, $lastModified = null)
    {
        $url = $this->apiUrl.'accounts/search';
        
        $data = [
            'pagination' => [
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'sortColumn' => 'Account ID',
                'sortDirection' => 'ASC'
            ],
            'searchFields' => [[
                'field' => 'Account ID',
                'operator' => 'NOT_BLANK'
            ]],
            'outputFields' => $this->accountFields
        ];
        
        if ($lastModified) {
            $data['searchFields'][] = [
                'field' => 'Account Last Modified Date/Time',
                'operator' => 'GREATER_AND_EQUAL',
                'value' => $lastModified
            ];
            
            $url.= '&lastModified='.$lastModified;
        }
        
        return $this->handleRequest($url, $data, __FUNCTION__, 'POST');
    }
    
    public function searchDonations($currentPage = 0, $pageSize = 200, $lastModified = null)
    {
        $url = $this->apiUrl.'donations/search';
        
        $data = [
            'pagination' => [
                'currentPage' => $currentPage,
                'pageSize' => $pageSize,
                'sortColumn' => 'Donation ID',
                'sortDirection' => 'ASC'
            ],
            'searchFields' => [[
                'field' => 'Donation ID',
                'operator' => 'NOT_BLANK'
            ], [
                'field' => 'Donation Status',
                'operator' => 'EQUAL',
                'value' => 'SUCCEEDED'
            ]],
            'outputFields' => $this->donationFields
        ];
        
        if ($lastModified) {
            $data['searchFields'][] = [
                'field' => 'Donation Last Modified Time',
                'operator' => 'GREATER_AND_EQUAL',
                'value' => $lastModified
            ];
            
            $url.= '&lastModified='.$lastModified;
        }
        
        return $this->handleRequest($url, $data, __FUNCTION__, 'POST');
    }
    
    public function getCampaigns()
    {
        $url = $this->apiUrl.'campaigns';
        
        return $this->handleRequest($url, null, __FUNCTION__);
    }

    /**
     * Mot used
     * @return type
     */
    public function getAccountsFromFile()
    {
        ini_set('memory_limit', '512M');
        
        $file = storage_path('/app/public/uploads/academy_contacts.xlsx');
        $rawData = Excel::load($file)->get();
        
        dd($rawData->count());
        
        $data = [];
        
        foreach ($rawData as $cur) {
            if (!array_get($cur, 'transaction_id')) {
                $name = array_get($cur, 'name');
            } else {
                array_set($cur, 'name', $name);
                $data[] = $cur;
            }
        }
        
        return $data;
    }
}
