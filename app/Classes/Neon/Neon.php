<?php

namespace App\Classes\Neon;

use App\Models\Address;
use App\Models\AltId;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Family;
use App\Models\Folder;
use App\Models\Note;
use App\Models\PaymentOption;
use App\Models\Purpose;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Traits\AlternativeIdTrait;
use App\Traits\DocumentsTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

/***
 * We are currently getting data from file since the api does not seem to work
 */
class Neon extends NeonAPI
{
    use AlternativeIdTrait, DocumentsTrait;
    
    private $pageSize = 200; // Cannot take more than 200 at a time
    
    public function sync($date)
    {
        ini_set('max_execution_time', 600);
        
//        $this->syncContacts($date);
//        $this->syncCampaigns();
//        $this->syncTransactions($date);
    }
    
    public function syncContacts($date) 
    {
        $total = $this->searchAccounts(0, $this->pageSize);
        
        $totalContacts = array_get($total, 'pagination.totalResults');
        
        if ($totalContacts === 0) {
            return 'No new contacts';
        }
        
        for ($i = 0; $i < array_get($total, 'pagination.totalPages'); $i++) {
            $neonContacts = $this->searchAccounts($i, $this->pageSize);
            
            if (array_get($neonContacts, 'pagination.totalResults') > 0) {
                foreach (array_get($neonContacts, 'searchResults') as $contact) {
                    $this->importContact($contact);
                }
            }
        }
    }
    
    public function importContact($neonData)
    {
        $altId = array_get($neonData, 'Account ID');
        
        if (empty($altId)) {
            return false;
        }
        
        $contactData = [
            'first_name' => nullIfEmpty(array_get($neonData, 'First Name')),
            'last_name' => nullIfEmpty(array_get($neonData, 'Last Name')),
            'middle_name' => nullIfEmpty(array_get($neonData, 'Middle Name')),
            'salutation' => nullIfEmpty(array_get($neonData, 'Prefix')),
            'gender' => nullIfEmpty(array_get($neonData, 'Gender')),
            'dob' => $this->getDob($neonData),
            'email_1' => $this->getEmail1($neonData),
            'email_2' => $this->getEmail2($neonData),
            'unsubscribed' => $this->getUnsubscribed($neonData),
            'active' => 1,
            'position' => nullIfEmpty(array_get($neonData, 'Job Title')),
            'company' => nullIfEmpty(array_get($neonData, 'Company Name')),
            'type' => array_get($neonData, 'Account Type') === 'Company' ? 'organization' : 'person',
            'preferred_name' => nullIfEmpty(array_get($neonData, 'Preferred Name')),
            'cell_phone' => nullIfEmpty(str_replace(' ', '', array_get($neonData, 'Phone 1 Full Number (F)'))),
            'home_phone' => nullIfEmpty(str_replace(' ', '', array_get($neonData, 'Phone 2 Full Number (F)'))),
            'other_phone' => nullIfEmpty(str_replace(' ', '', array_get($neonData, 'Phone 3 Full Number (F)')))
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
        
        // Contact does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            // Check first if we already have a contact with same email, if yes than use that
            if (!empty(array_get($contactData, 'email_1'))) {
                $contact = Contact::where('email_1', array_get($contactData, 'email_1'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from Neon
                    // Doing this because email in Neon might not be unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'Neon')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_1'] = null; // Unset email 1
                    }
                }
            }
            
            if (empty($contact)) {
                $contact = new Contact();
            }
            
            $contact = mapModel($contact, $contactData);

            if (!auth()->user()->tenant->contacts()->save($contact)) {
                abort(500);
            }
            
            $contact->refresh();
            
            $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), [
                'alt_id' => $altId,
                'label' => array_get($contact, 'full_name'),
                'system_created_by' => 'Neon'
            ]);
        } else {
            $contact = Contact::find(array_get($altIdObject, 'relation_id'));
            
            if ($contact) {
                mapModel($contact, $contactData);
                $contact->update();
            }
        }
        
        if ($contact) {
            $this->importContactAddress($contact, $neonData);
            
            $this->importContactNote($contact, $neonData);
        }
    }
    
    public function importContactAddress(Contact $contact, $data)
    {
        $contact->addresses()->delete();
        
        if (array_get($data, 'Address Line 1') || array_get($data, 'City') || array_get($data, 'State/Province')) {
            $addressData = [
                'mailing_address_1' => nullIfEmpty(array_get($data, 'Address Line 1')),
                'mailing_address_2' => nullIfEmpty(array_get($data, 'Address Line 2')),
                'city' => nullIfEmpty(array_get($data, 'City')),
                'region' => nullIfEmpty(array_get($data, 'State/Province')),
                'postal_code' => nullIfEmpty(array_get($data, 'Full Zip Code (F)')),
                'country' => 'US',
                'is_residence' => array_get($data, 'Address Type') === 'Work' ? 0 : 1,
                'is_mailing' => 1,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];
            
            $address = mapModel(new Address(), $addressData);
            $address->save();
        }
    }
    
    public function importContactNote(Contact $contact, $data)
    {
        $contact->notes()->delete();
        
        if (array_get($data, 'Note Text') || array_get($data, 'Note Title')) {
            $noteData = [
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'user_id' => array_get(auth()->user(), 'id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class,
                'date' => date('Y-m-d')
            ];
            
            if (array_get($data, 'Note Title')) {
                $noteData['title'] = array_get($data, 'Note Title');
                $noteData['content'] = nullIfEmpty(array_get($data, 'Note Text'));
            } elseif (array_get($data, 'Note Text')) {
                $noteData['title'] = array_get($data, 'Note Text');
            }
            
            $note = mapModel(new Note(), $noteData);
            $note->save();
        }
    }
    
    public function syncCampaigns()
    {
        $neonCampaigns = $this->getCampaigns();

        if ($neonCampaigns) {
            foreach ($neonCampaigns as $campaign) {
                $this->importCampaign($campaign);
            }
        }
    }
    
    public function importCampaign($neonData)
    {
        $altId = array_get($neonData, 'id');
        
        if (empty($altId)) {
            return false;
        }
        
        $purpose = Purpose::where('sub_type', 'organizations')->first();
        
        $campaignData = [
            'purpose_id' => array_get($purpose, 'id'),
            'name' => nullIfEmpty(array_get($neonData, 'name')),
            'receive_donations' => array_get($neonData, 'status') === 'ACTIVE' ? 1 : 0,
            'page_type' => 'givingpage',
            'sub_type' => 'givingpages'
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Campaign::class);
        
        // Campaign does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            $campaign = new Campaign();
            mapModel($campaign, $campaignData);

            if (!auth()->user()->tenant->campaigns()->save($campaign)) {
                abort(500);
            }
            
            $campaign->refresh();
            
            $this->alternativeIdCreate(array_get($campaign, 'id'), get_class($campaign), [
                'alt_id' => $altId,
                'label' => array_get($campaign, 'name'),
                'system_created_by' => 'Neon'
            ]);
        } else {
            $campaign = Campaign::find(array_get($altIdObject, 'relation_id'));
            
            if ($campaign) {
                mapModel($campaign, $campaignData);
                $campaign->update();
            }
        }
    }
    
    public function syncTransactions($date)
    {
        $total = $this->searchDonations(0, $this->pageSize);
        
        $totalTransactions = array_get($total, 'pagination.totalResults');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        for ($i = 0; $i < array_get($total, 'pagination.totalPages'); $i++) {
            $neonTransactions = $this->searchDonations($i, $this->pageSize);
            
            if (array_get($neonTransactions, 'pagination.totalResults') > 0) {
                foreach (array_get($neonTransactions, 'searchResults') as $transaction) {
                    $this->importTransaction($transaction);
                }
            }
        }
    }
    
    public function importTransaction($neonData)
    {
        $altId = array_get($neonData, 'Donation ID');
        
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $transactionTime = nullIfEmpty(array_get($neonData, 'Donation Date').' 12:00:00');
        if (!is_null($transactionTime)) {
            // TODO - Find a better way to handle localization according to tenant local time
            $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
        }
        
        $transactionTemplateData = [
            'completion_datetime' => $transactionTime,
            'amount' => array_get($neonData, 'Donation Amount'),
            'is_recurring' => array_get($neonData, 'Recurring Donation') === 'Yes' ? 1 : 0,
            'is_pledge' => 0,
            'successes' => 1,
            'acknowledged' => 0
        ];
        
        $transactionData = [
            'transaction_initiated_at' => $transactionTime,
            'transaction_last_updated_at' => $transactionTime,
            'channel' => 'unknown',
            'check_number' => nullIfEmpty(onlyNumbers(array_get($neonData, 'Check Number'))),
            'system_created_by' => 'Neon',
            'status' => 'complete',
            'transaction_path' => 'neon',
            'anonymous_amount' => 'protected',
            'anonymous_identity' => 'protected',
            'type' => 'donation',
            'acknowledged' => 0,
            'acknowledged_at' => null,
            'tax_deductible' => array_get($neonData, 'Non-Deductible Amount') > 0 ? 0 : 1,
            'comment' => array_get($neonData, 'Donor Note')
        ];
        
        $contactAltid = array_get($neonData, 'Account ID');
        if (!empty($contactAltid)) {
            $contactAltidObject = $this->alternativeIdRetrieve($contactAltid, Contact::class);
            if (!empty($contactAltidObject)) {
                $transactionTemplateData['contact_id'] = array_get($contactAltidObject, 'relation_id');
                $transactionData['contact_id'] = array_get($contactAltidObject, 'relation_id');
            }
        }
        
        if (!array_get($transactionData, 'contact_id')) {
            return false;
        }
        
        $contact = Contact::find(array_get($transactionData, 'contact_id'));
        
        if (!$contact) {
            return false;
        }
        
        $transactionSplitData = [];
        
        $purpose = Purpose::where('sub_type', 'organizations')->first();
        $purposeId = array_get($purpose, 'id');
        $campaignId = $this->getCampaignId($neonData);

        $transactionSplitData[] = [
            'template' => [
                'campaign_id' => $campaignId,
                'purpose_id' => $purposeId,
                'tax_deductible' => array_get($neonData, 'Non-Deductible Amount') > 0 ? 0 : 1,
                'type' => 'donation',
                'amount' => array_get($neonData, 'Donation Amount'),
                'splitAltId' => array_get($neonData, 'Donation ID')
            ],
            'transaction' => [
                'campaign_id' => $campaignId,
                'purpose_id' => $purposeId,
                'amount' => array_get($neonData, 'Donation Amount'),
                'type' => 'donation',
                'tax_deductible' => array_get($neonData, 'Non-Deductible Amount') > 0 ? 0 : 1,
                'splitAltId' => array_get($neonData, 'Donation ID')
            ]
        ];
        
        // Transaction does not exist so we make a new one, else do nothing
        if (empty($altIdObject)) {
            if ($transactionData['contact_id']) {
                $transactionData['payment_option_id'] = $this->getPaymentOptionId($transactionData['contact_id'], $neonData);
            }
            
            $transactionTemplate = $this->storeTransactionTemplate($transactionTemplateData, $altId);
            
            $transactionData['transaction_template_id'] = array_get($transactionTemplate, 'id');
            
            $transaction = $this->storeTransaction($transactionData, $altId);
            
            foreach ($transactionSplitData as $split) {
                $split['template']['transaction_template_id'] = array_get($transactionTemplate, 'id');
                $splitTemplate = $this->storeTransactionTemplateSplit($split['template']);
            
                $split['transaction']['transaction_id'] = array_get($transaction, 'id');
                $split['transaction']['transaction_template_split_id'] = array_get($splitTemplate, 'id');
                $this->storeTransactionSplit($split['transaction']);
            }
        }
    }
    
    public function getCampaignId($data)
    {
        $campaingId = array_get($data, 'Campaign ID');
        
        if (empty($campaingId)) {
            return 1;
        }
        
        $campaignAltObject = $this->alternativeIdRetrieve($campaingId, Campaign::class);
        
        if (empty($campaignAltObject)) {
            return 1;
        } else {
            return array_get($campaignAltObject, 'relation_id');
        }
    }
    
    public function getPaymentOptionId($contactId, $neonData)
    {
        if (!$contactId) {
            return null;
        }
        
        $paymentOptionData = ['contact_id' => $contactId];
        
        if (array_get($neonData, 'Credit Card Last 4 Digits')) {
            $paymentOptionData['category'] = 'cc';
            $paymentOptionData['last_four'] = array_get($neonData, 'Credit Card Last 4 Digits', '****');
        } elseif (array_get($neonData, 'Bank Account Last 4 Digits')) {
            $paymentOptionData['category'] = 'ach';
            $paymentOptionData['last_four'] = array_get($neonData, 'Bank Account Last 4 Digits', '****');
        } else {
            $paymentOptionData['category'] = 'unknown';
            $paymentOptionData['last_four'] = null;
        }
        
        if ($paymentOptionData['last_four'] === '') {
            $paymentOptionData['last_four'] = '****';
        }
        
        $paymentOption = PaymentOption::where([
            ['contact_id', '=', $contactId],
            ['category', '=', $paymentOptionData['category']],
        ])->where(function ($q) use ($paymentOptionData) {
            $q->where('last_four', $paymentOptionData['last_four'])->orWhereNull('last_four');
        })->first();

        if (is_null($paymentOption)) {
            $paymentOption = mapModel(new PaymentOption(), $paymentOptionData);
            auth()->user()->tenant->paymentOptions()->save($paymentOption);
        }
        
        return array_get($paymentOption, 'id');
    }
   
    public function storeTransactionTemplate($data, $altId)
    {
        $transactionTemplate = new TransactionTemplate();
        mapModel($transactionTemplate, $data);

        if (!auth()->user()->tenant->transactionTemplates()->save($transactionTemplate)) {
            abort(500);
        }

        $transactionTemplate->refresh();

        $this->alternativeIdCreate(array_get($transactionTemplate, 'id'), get_class($transactionTemplate), [
            'alt_id' => $altId,
            'label' => 'Transaction Template',
            'system_created_by' => 'Neon'
        ]);
        
        return $transactionTemplate;
    }
    
    public function storeTransaction($data, $altId)
    {
        $transaction = new Transaction();
        mapModel($transaction, $data);

        if (!auth()->user()->tenant->transactions()->save($transaction)) {
            abort(500);
        }

        $transaction->refresh();

        $this->alternativeIdCreate(array_get($transaction, 'id'), get_class($transaction), [
            'alt_id' => $altId,
            'label' => 'Transaction',
            'system_created_by' => 'Neon'
        ]);
        
        return $transaction;
    }
    
    public function storeTransactionTemplateSplit($data)
    {
        $split = new TransactionTemplateSplit();
        mapModel($split, $data);

        if (!auth()->user()->tenant->transactionTemplateSplits()->save($split)) {
            abort(500);
        }

        $split->refresh();

        $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), [
            'alt_id' => array_get($data, 'splitAltId'),
            'label' => 'Transaction Template Split',
            'system_created_by' => 'Neon'
        ]);
        
        if (array_get($data, 'appealData')) {
            $this->tagAppeal($split, array_get($data, 'appealData.Name'));
        }
        
        return $split;
    }
    
    public function storeTransactionSplit($data)
    {
        $split = new TransactionSplit();
        mapModel($split, $data);

        if (!auth()->user()->tenant->transactionSplits()->save($split)) {
            abort(500);
        }

        $split->refresh();

        $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), [
            'alt_id' => array_get($data, 'splitAltId'),
            'label' => 'Transaction Split',
            'system_created_by' => 'Neon'
        ]);
        
        if (array_get($data, 'appealData')) {
            $this->tagAppeal($split, array_get($data, 'appealData.Name'));
        }
        
        return $split;
    }
    
    private function getDob($data)
    {
        $year = array_get($data, 'DOB Year');
        $month = str_pad(array_get($data, 'DOB Month'), 2, '0', STR_PAD_LEFT);
        $day = array_get($data, 'DOB Day');
        
        if ($year && $month && $day) {
            return $year.'-'.$month.'-'.$day;
        } else {
            return null;
        }
    }
    
    private function getEmail1($data)
    {
        $email1 = array_get($data, 'Email 1');
        $email2 = array_get($data, 'Email 2');
        $email3 = array_get($data, 'Email 3');
        
        if ($email1) {
            return $email1;
        } elseif ($email2) {
            return $email2;
        } elseif ($email3) {
            return $email3;
        } else {
            return null;
        }
    }
    
    private function getEmail2($data)
    {
        $email1 = array_get($data, 'Email 1');
        $email2 = array_get($data, 'Email 2');
        $email3 = array_get($data, 'Email 3');
        
        if ($email1 && $email2 && $email3) {
            return $email2.','.$email3;
        } elseif ($email1 && $email2 && !$email3) {
            return $email2;
        } elseif (!$email1 && $email2 && $email3) {
            return $email3;
        } else {
            return null;
        }
    }
    
    private function getUnsubscribed($data)
    {
        $emailOptOut = array_get($data, 'Email Opt-Out');
        
        if ($emailOptOut === 'No' || $emailOptOut === 'N/A') {
            return null;
        } else {
            return date('Y-m-d H:i:s');
        }
    }
}
