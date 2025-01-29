<?php

namespace App\Classes;

use Illuminate\Support\Facades\Request;
use App\Classes\MpWrapper\RequestClient as Client;
use App\Constants;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use Jenssegers\Agent\Agent;
use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Models\PaymentOption;

use App\Traits\AlternativeIdTrait;
use App\Traits\Transactions\PurposeTrait;
use App\Traits\Transactions\ContactTrait;
use App\Traits\Transactions\CampaignTrait;
use App\Traits\Transactions\TransactionTemplateTrait;
use App\Traits\Transactions\TransactionTemplateSplitTrait;
use App\Traits\TagsTrait;
use App\Classes\MissionPillarsLog;

/**
 * Description of ApiTransaction
 *
 * @author josemiguel
 */
class Transactions {

    use AlternativeIdTrait, PurposeTrait, 
        ContactTrait, CampaignTrait, TransactionTemplateTrait,
        TransactionTemplateSplitTrait, TagsTrait;

    private $c2gIntegration;
    private $page = 0;

    public function __construct($token = null) {
        $this->c2gIntegration = new ContinueToGiveIntegration($token);
    }

    /**
     * Store single transaction
     * @param Array $data
     * @return App\Models\Transaction | null
     */
    public function singleTransaction($data = null) {
        if ($data) {
            return $this->transaction($data);
        }
        return null;
    }

    /**
     * Executes multiple transaction process
     * @todo conform the right url with pages
     */
    public function executeTransactions($params = []) {
        $data = $this->c2gIntegration->getTransactions($params);
        if (!is_null(array_get($data, 'status_code'))) { //error on request
            if(auth()->check()){
                return redirect()->route('dashboard.index');
            }
            abort(500);
        }

        if ($data) {
            $transaction = null;
            $pages = array_get($data, 'meta.pagination.total_pages', 0);
            for ($i = 1; $i <= $pages; $i++) {
                array_set($params, 'page', $i);
                $json = $this->c2gIntegration->getTransactions($params);
                $transaction = $this->saveTransactions($json);
            }
            
            if ($pages == 1) {
                return $transaction;
            }
        }
    }

    /**
     * Process all transactions in current json object
     * @param Array $json
     */
    public function saveTransactions($json = null) {
        if ($json) {
            $dataset = array_get($json, 'data', null);
            $transaction = null;
            foreach ($dataset as $data) {
                try {
                    $transaction = $this->transaction($data);
                } catch (\Exception $ex) {
                    MissionPillarsLog::exception($ex, json_encode($data));
                } catch (\Illuminate\Database\QueryException $ex){
                    MissionPillarsLog::exception($ex, json_encode($data));
                }
            }
            
            return $transaction;
        }
    }

    /**
     * Store transaction
     * @param Array $data
     * @return App\Models\Transaction
     */
    private function transaction($data) {
        
        $campaign = null;

        $toCampaign = array_get($data, 'toCampaign.data', null);
        $forCampaign = array_get($data, 'forCampaign.data', null);
        $throughCampaign = array_get($data, 'thruCampaign.data', null);

        $purposeTo = $this->savePurposeData($toCampaign, $data);
        $purposeFor = $this->savePurposeData($forCampaign, $data);

        if (array_get($toCampaign, 'alt_id') != array_get($forCampaign, 'alt_id')) {
            array_set($purposeFor, 'parent_purposes_id', array_get($purposeTo, 'id', null));
            $purposeFor->update();
        }

        if (array_get($toCampaign, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY) {
            $person = array_get($toCampaign, 'contact.data', null);
            $this->setMissionary($person, $purposeTo, $purposeFor);
        }
        
        if (array_get($throughCampaign, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_GIVINGPAGES) {
            $fundraiser = null;
            $fundraiser = (array_get($throughCampaign, 'contact.data.sub_type') != 'organizations') 
                ? array_get($throughCampaign, 'contact.data', null) : null;
            $campaign = $this->setCampaign($fundraiser, $purposeTo, $purposeFor, $throughCampaign);
        }

        $tags = [
            array_get($purposeTo, 'tag.id'),
            array_get($purposeFor, 'tag.id')
        ];
        
        if (array_get($data, 'transaction_split.data.type') !== 'purchase') {
            $tags[] = array_get(Constants::TAG_SYSTEM, 'TAGS.DONOR');
        }
        
        $person = array_get($data, 'giver.data');
//        $user = $this->saveUserData($person);
//        if(!is_null($user)){
//            array_set($person, 'user_id', array_get($user, 'id'));
//        }
        $donor = $this->saveContactData($person, $tags);

        $payment = array_get($person, 'payment_options.data');
        $altPayment = $this->alternativeIdRetrieve(array_get($payment, 'alt_id', 0), PaymentOption::class);
        if (!$altPayment) {
            $paymentOption = mapModel(new PaymentOption(), $payment);
            array_set($paymentOption, 'contact_id', array_get($donor, 'id'));
            if(!array_has($payment, 'first_four')){
                array_set($paymentOption, 'first_four', substr(array_get($payment, 'card_number'), 0, 4));
            }
            
            if(!array_has($payment, 'last_four')){
                array_set($paymentOption, 'last_four', substr(array_get($payment, 'card_number'), -4, 4));
            }
            
            if (auth()->user()->tenant->paymentOptions()->save($paymentOption)) {
                $fields = ['alt_id' => array_get($payment, 'alt_id'), 'label' => array_get($payment, 'card_type'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                $altPayment = $this->alternativeIdCreate(array_get($paymentOption, 'id'), get_class($paymentOption), $fields);
            }
        }
        else{
            $paymentOption = array_get($altPayment, 'getRelationTypeInstance');
        }
        
        //start new transaction logic with contact_id
        $template = array_get($data, 'transaction_template.data');
        array_set($template, 'contact_id', array_get($donor, 'id'));
        $transactionTemplate = $this->transactionTemplateStore($template);
        $transactionTemplateSplit = $this->transactionTemplateSplitStore($transactionTemplate, array_get($data, 'transaction_template_split.data'), $campaign, $purposeFor);
        if($transactionTemplate && (bool)array_get($transactionTemplate, 'is_recurring', false)){
            array_push($tags, array_get(Constants::TAG_SYSTEM, 'TAGS.RECURRING_TRANSACTION'));
            $this->tagContact($donor, $tags);
        }
        
        $transaction = $this->saveTransactionData($data, $purposeFor, $campaign, $transactionTemplate, $donor, $paymentOption);
        $this->saveTransactionSplit($transaction, array_get($data, 'transaction_split.data'), $purposeFor, $campaign, $transactionTemplateSplit);
        
        if( !is_null($transaction) ){
            $this->mapTransactionToContactPledge($transaction, $purposeFor, $campaign, $donor);
        }
        
        return ['id' => array_get($transaction, 'id')];
    }

    private function saveTransactionSplit($transaction, $jTransactionSplit, $chart, $campaign, $transactionTemplateSplit) {
        
        if($transaction && $jTransactionSplit && $transactionTemplateSplit){
            $alt = $this->alternativeIdRetrieve(array_get($jTransactionSplit, 'alt_id', 0), TransactionSplit::class);
            if(!$alt){
                $split = mapModel(new TransactionSplit(), $jTransactionSplit);
                array_set($split, 'purpose_id', array_get($chart, 'id'));
                array_set($split, 'transaction_id', array_get($transaction, 'id'));
                //campaign_id can not be null because db joins return null results
                array_set($split, 'campaign_id', array_get($campaign, 'id', 1));
                array_set($split, 'transaction_template_split_id', array_get($transactionTemplateSplit, 'id'));
                if( auth()->user()->tenant->transactionSplits()->save($split) ){
                    $fields = [
                        'alt_id' => array_get($jTransactionSplit, 'alt_id'),
                        'label' => 'Transaction Split',
                        'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
                    ];
                    $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), $fields);
                    return $split;
                }
            }
            $split = $alt->getRelationTypeInstance;
            mapModel($split, $jTransactionSplit);
            array_set($split, 'purpose_id', array_get($chart, 'id'));
            array_set($split, 'transaction_id', array_get($transaction, 'id'));
            //campaign_id can not be null because db joins return null results
            array_set($split, 'campaign_id', array_get($campaign, 'id', 1));
            array_set($split, 'transaction_template_split_id', array_get($transactionTemplateSplit, 'id'));
            $split->update();
            return $split;
        }
        return null;
    }

    private function getMedium($url, $referrer) {
        $result = null;
        foreach (Constants::TRANSACTION_PATH as $path) {
            $result = array_get($path, "VALUE");
            $foundInUrl = strpos($url, array_get($path, "SEARCH_FOR"));
            $foundInReferrer = strpos($referrer, array_get($path, "SEARCH_FOR"));
            if (array_get($path, "SEARCH_IN") === 'url') {
                if ($foundInUrl) {
                    break;
                }
            }
            if (array_get($path, "SEARCH_IN") === 'referrer') {
                if ($foundInReferrer) {
                    break;
                }
            }
            if (array_get($path, "SEARCH_IN") === 'url,referrer') {
                if ($foundInUrl || $foundInReferrer) {
                    break;
                }
            }
        }
        return $result;
    }

    private function getChannel($medium)
    {
        $channel = null;
        
        switch ($medium) {
            case 'embedded form':
                $channel = 'ctg_embed';
                break;
            case 'text for link':
                $channel = 'ctg_text_link';
                break;
            case 'text':
                $channel = 'ctg_text_give';
                break;
            default :
                $channel = 'ctg_direct';
                break;
        }
        
        return $channel;
    }
    
    private function getTransactionMedium($jTransactionMedium) {
        $userAgent = array_get($jTransactionMedium, 'user_agent');
        $url = array_get($jTransactionMedium, 'url');
        if (!is_null($userAgent)) {
            $medium = [];
            $agent = new Agent();
            $agent->setUserAgent($userAgent);
            array_set($medium, 'os', $agent->platform());
            array_set($medium, 'browser', $agent->browser());
            array_set($medium, 'type', $agent->device());
            if ($agent->isPhone()) {
                array_set($medium, 'device_category', array_get(Constants::DEVICE_CATEGORY, 'PHONE'));
            }
            if ($agent->isTablet()) {
                array_set($medium, 'device_category', array_get(Constants::DEVICE_CATEGORY, 'TABLET'));
            }
            //there is no way to check if its laptop on user agent, because user agent string
            //sends 'Desktop browser'
            if ($agent->isDesktop()) {
                array_set($medium, 'device_category', array_get(Constants::DEVICE_CATEGORY, 'DESKTOP'));
            }

            $referrer = array_get($jTransactionMedium, 'referrer');
            $m = $this->getMedium($url, $referrer);
            $channel = $this->getChannel($m);

            array_set($medium, 'ip_address', array_get($jTransactionMedium, 'ipaddress'));
            array_set($medium, 'referrer', $referrer);
            array_set($medium, 'url', $url);
            array_set($medium, 'transaction_path', $m);
            array_set($medium, 'channel', $channel);
            return $medium;
        }
        return null;
    }

    /**
     * Store or retrieve Transaction
     * @param Array $jTransaction
     * @param App\Models\Purpose $chart
     * @param App\Models\Campaign $campaign
     * @param App\Models\RecurringTransaction $transactionTemplate
     * @param App\Models\Contact $contact
     * @return App\Models\Transaction | null
     */
    private function saveTransactionData($jData = null, $chart = null, $campaign = null, $transactionTemplate = null, $contact = null, $paymentOption = null) {
        $jTransaction = array_get($jData, 'transaction.data');
        $jTransactionMedium = array_get($jData, 'transactionMedium.data');

        if ($jTransaction && $contact) {
            $altId = $this->alternativeIdRetrieve(array_get($jTransaction, 'alt_id', 0), Transaction::class);
            if (!$altId) {
                $transaction = mapModel(new Transaction(), $jTransaction);
                array_set($transaction, 'contact_id', array_get($contact, 'id'));
                array_set($transaction, 'transaction_template_id', array_get($transactionTemplate, 'id'));
                array_set($transaction, 'system_created_by', Constants::DEFAULT_SYSTEM_CREATED_BY);
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));

                $medium = $this->getTransactionMedium($jTransactionMedium);
                mapModel($transaction, $medium);

                if (auth()->user()->tenant->transactions()->save($transaction)) {
                    $this->deviceTag($transaction, $contact);
                    $this->pathTag($transaction, $contact);
                    
                    $fields = [
                        'alt_id' => array_get($jTransaction, 'alt_id'),
                        'label' => 'Transaction',
                        'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
                    ];
                    $this->alternativeIdCreate(array_get($transaction, 'id'), get_class($transaction), $fields);
                    return $transaction;
                }
            }
            $transaction = $altId->getRelationTypeInstance;
            mapModel($transaction, $jTransaction);
            $transaction->update();
            return $transaction;
        }
        return null;
    }
    
    private function pathTag($transaction, $contact) {
        $folderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.TRANSACTION_PATHS');
        $tagName = array_get($transaction, 'transaction_path', Constants::DEFAULT_SYSTEM_CREATED_BY);
        
        $tag = $this->tagExists($tagName, $folderId);
        if (!$tag) {
            $tag = $this->setTag($transaction, $folderId, true, $tagName);
        }

        $this->tagContact($contact, [$tag->id], false);
    }
    
    private function deviceTag($transaction, $contact) {
        $folderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.DEVICES');
        $tagName = array_get($transaction, 'device_category', Constants::DEFAULT_SYSTEM_CREATED_BY);
        
        $tag = $this->tagExists($tagName, $folderId);
        if (!$tag) {
            $tag = $this->setTag($transaction, $folderId, true, $tagName);
        }

        $this->tagContact($contact, [$tag->id], false);
    }

    /**
     * Retrieves JSON data from url and token provided
     * @param string $url
     * @param boolean $asArray
     * @return Array | null
     */
    public function getJsonData($endpoint, $array = false) {
        if ($endpoint) {
            $client = new Client();
            $response = $client->request('GET', $endpoint);
            $content = $response->getBody()->getContents();

            if (gettype($content) === 'string') {
                $dataset = json_decode($content, $array);
                return $dataset;
            }
        }
        return null;
    }

}
