<?php

namespace App\Traits\Transactions;

use App\Models\Campaign;
use App\Models\Document;
use App\Models\TransactionTemplate;
use App\Models\TransactionTemplateSplit;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use App\Models\Purpose;
use Carbon\Carbon;
use App\Models\PaymentOption;
use App\Traits\Transactions\TransactionTemplateTrait;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;

/**
 *
 * @author josemiguel
 */
trait Transactions {
    use MatchesContactPledges;
    
    private $default_status = 'complete';

    public function processTransactionUpdate($split, $fields, $isPledge = false) {
        $transaction = null;
        $transactionSplit = null;
        if($isPledge){
            $transactionTemplate = $this->updateTransactionTemplate(array_get($split, 'template'), $fields, $isPledge);
            $transactionTemplateSplit = $this->updateTransactionTemplateSplit($split, $fields, $transactionTemplate);
        }
        else{
            $transactionTemplate = $this->updateTransactionTemplate(array_get($split, 'transaction.template'), $fields, $isPledge);
            $transaction = $this->updateTransaction(array_get($split, 'transaction'), $fields, $transactionTemplate);
            
            foreach (array_get($fields, 'splits') as $split) {
                $tags = array_get($split, 'tags');
                $tagIds = [];
                foreach ($tags as $tag) {
                    $tagIds[] = array_get($tag, 'id');
                }
                array_set($split, 'tags', $tagIds);
                array_set($split, 'type', array_get($fields, 'type'));
                
                if (array_get($split, 'id')) { // update existing
                    $transactionSplitDb = TransactionSplit::findOrFail(array_get($split, 'id'));
                    $transactionTemplateSplit = $this->updateTransactionTemplateSplit($transactionSplitDb->transactionTemplateSplit, $split, $transactionTemplate);
                    $transactionSplit = $this->updateTransactionSplit($transactionSplitDb, $split, $transaction);
                } else { // create new
                    array_set($split, 'campaign_id', array_get($split, 'campaign.id', 1));
                    array_set($split, 'purpose_id', array_get($split, 'chart.id'));
                    $transactionTemplateSplit = $this->storeTransactionTemplateSplit($split, $transactionTemplate);
                    array_set($split, 'transaction_template_split_id', array_get($transactionTemplateSplit, 'id'));
                    $this->storeTransactionSplit($split, $transaction, $transactionTemplateSplit);
                }
            }
        }

        $result = [];
        array_push($result, $transactionTemplate);
        array_push($result, $transactionTemplateSplit);
        array_push($result, $transaction);
        array_push($result, $transactionSplit);
        
        // TODO fix softcredits
//        if (array_get($fields, 'softCredits') && count(array_get($fields, 'softCredits')) > 0) {
//            $this->storeSoftCredits($fields, $transaction);
//        }
        
        if (array_get($fields, 'attachments') && count(array_get($fields, 'attachments')) > 0) {
            $this->storeAttachments($fields, $transaction);
        }
        
        return $result;
    }

    public function updateTransactionTemplate($transactionTemplate, $fields, $isPledge = false) {
        if(array_get($fields, 'update_recurring') === 'false'){
            $fields = array_except($fields, 'is_recurring');
        }

        $totalAmount = 0;
        foreach (array_get($fields, 'splits') as $split) {
            $totalAmount+= array_get($split, 'amount');
        }
        array_set($fields, 'amount', $totalAmount);
        
        mapModel($transactionTemplate, $fields);
        array_set($transactionTemplate, 'is_pledge', $isPledge);
        if(array_get($fields, 'is_recurring') === '0'){
            array_set($transactionTemplate, 'billing_cycles', null);
            array_set($transactionTemplate, 'billing_period', null);
            array_set($transactionTemplate, 'billing_frequency', null);
        }
        $transactionTemplate->update();
        return $transactionTemplate;
    }

    public function updateTransaction($transaction, $fields, $transactionTemplate) {
        $category = array_get($fields, 'category');
        
        if (array_get($fields, 'deposit_date')) {
            array_set($fields, 'deposit_date', substr(array_get($fields, 'deposit_date'), 0, 10));
        }
        
        //dd($fields, $transaction, $transactionTemplate, $category);
        mapModel($transaction, $fields);

        if( $category === 'check' ){
            if( array_get($fields, 'payment_option_id', 0) <= 0 ){
                $paymentOption = PaymentOption::where([
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['category', '=', 'check'],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption)){
                    $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type'));
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));
            }

            $transaction->update();
        }

        if (in_array($category, ['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'])) {
            $paymentOption = PaymentOption::where([
                ['category', '=', $category],
                ['contact_id', '=', array_get($fields, 'contact_id')]
            ])->first();

            if(is_null($paymentOption) ){
                $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type', 'first_four', 'last_four'));
                auth()->user()->tenant->paymentOptions()->save($paymentOption);
            }

            array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
            $transaction->update();
        }
        if( $category === 'ach' ){
            if(array_get($fields, 'payment_option_id') == '0'){
                $paymentOption = PaymentOption::where([
                    ['category', '=', 'ach'],
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption) ){
                    $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type', 'first_four'));
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
                $transaction->update();
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));
                $transaction->update();
            }
        }
        if( $category === 'cc' ){
            if(array_get($fields, 'payment_option_id') == '0'){
                $paymentOption = PaymentOption::where([
                    ['category', '=', 'cc'],
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['first_four', '=', array_get($fields, 'first_four')],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption) ){
                    $paymentOption = mapModel(new PaymentOption(), $fields);
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
                $transaction->update();
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));

                $transaction->update();
            }
        }
        
        if ($category === 'soft_credit') {
            $transaction->update();
        }
        
        $this->storeAutoTags($fields);
        
        return $transaction;
    }

    public function updateTransactionTemplateSplit($transactionTemplateSplit, $fields, $transactionTemplate) {
        if(array_get($fields, 'update_recurring') ){
            $fields = array_except($fields, 'amount');
        }
        
        mapModel($transactionTemplateSplit, $fields);
        array_set($transactionTemplateSplit, 'transaction_template_id', array_get($transactionTemplate, 'id'));
        $transactionTemplateSplit->update();
        if (array_has($fields, 'tags')) {
            $transactionTemplateSplit->tags()->sync($fields['tags']);
        }

        return $transactionTemplateSplit;
    }

    public function updateTransactionSplit($transactionSplit, $fields, $transaction) {
        mapModel($transactionSplit, $fields);
        array_set($transactionSplit, 'transaction_id', array_get($transaction, 'id'));
        array_set($transactionSplit, 'tax_deductible', array_get($fields, 'tax_deductible', false));
        $transactionSplit->update();
        if (array_has($fields, 'tags')) {
            $transactionSplit->tags()->sync($fields['tags']);
        }

        return $transactionSplit;
    }

    public function processTransactionStore($fields, $isPledge = false) {
        $pledge = null;
        
        if(!is_null(array_get($fields, 'master_id'))){
            $pledge = TransactionTemplate::findOrFail(array_get($fields, 'master_id'));
            if(array_get($pledge, 'status') === 'complete'){
                return ['status' => 'pledge-completed'];
            }
        }

        $transactionTemplate = $this->storeTransactionTemplate($fields, $isPledge);
        
        $result = [];
        array_set($result, 'transactionTemplate', $transactionTemplate);
        
        $transaction = null;
        if(!$isPledge){
            $transaction = $this->storeTransaction($fields, $transactionTemplate);
            array_set($result, 'transaction', $transaction);
        }

        $hasSoftCredits = false;
        
        foreach (array_get($fields, 'splits') as $split) {
            $tags = array_get($split, 'tags');
            $tagIds = [];
            foreach ($tags as $tag) {
                $tagIds[] = array_get($tag, 'id');
            }
            array_set($split, 'tags', $tagIds);
            array_set($split, 'type', array_get($fields, 'type'));
            array_set($split, 'campaign_id', array_get($split, 'campaign.id', 1));
            array_set($split, 'purpose_id', array_get($split, 'chart.id'));
            $transactionTemplateSplit = $this->storeTransactionTemplateSplit($split, $transactionTemplate);
            array_set($split, 'transaction_template_split_id', array_get($transactionTemplateSplit, 'id'));
            $this->storeTransactionSplit($split, $transaction, $transactionTemplateSplit);
            
            if (count(array_get($split, 'softCredits', [])) > 0) {
                $hasSoftCredits = true;
            }
        }
        
        //we check if contact has a pledge
        $contact = Contact::find(array_get($fields, 'contact_id'));
        if($contact && !$pledge){
            $purpose_id = $transactionTemplateSplit->purpose_id ?:1;
            $campaign_id = $transactionTemplateSplit->campaign_id ?:1;
            $pledge = $this->findFirstMatchingPledge($contact, $purpose_id, $campaign_id);
        }
        if($pledge && $transaction){
            $pledge->addPledgedTransaction($transaction);
        }
        
        $this->storeAutoTags($fields);

        // TODO fix softcredits
//        if ($hasSoftCredits) {
//            $this->storeSoftCredits($fields, $transaction);
//        }
        
        if (array_get($fields, 'attachments') && count(array_get($fields, 'attachments')) > 0) {
            $this->storeAttachments($fields, $transaction);
        }
        
        return $result;
    }

    public function storeTransactionTemplate($fields, $isPledge = false) {
        $totalAmount = 0;
        foreach (array_get($fields, 'splits') as $split) {
            $totalAmount+= array_get($split, 'amount');
        }
        array_set($fields, 'amount', $totalAmount);
        
        $transactionTemplate = mapModel(new TransactionTemplate(), $fields);
        array_set($transactionTemplate, 'is_pledge', $isPledge);
        array_set($transactionTemplate, 'billing_start_date', setUTCDateTime(array_get($fields, 'billing_start_date')));
        array_set($transactionTemplate, 'billing_end_date', setUTCDateTime(array_get($fields, 'billing_end_date')));
        if(auth()->check()){
            auth()->user()->tenant->transactionTemplates()->save($transactionTemplate);
        }
        else{
            $contact = \App\Models\Contact::withoutGlobalScopes()->where('id', array_get($fields, 'contact_id'))->first();
            array_set($transactionTemplate, 'tenant_id', array_get($contact, 'tenant_id'));
            $transactionTemplate->save();
        }

        return $transactionTemplate;
    }

    public function storeTransactionTemplateSplit($fields, $transactionTemplate) {
        $transactionTemplateSplit = mapModel(new TransactionTemplateSplit(), $fields);
        array_set($transactionTemplateSplit, 'transaction_template_id', array_get($transactionTemplate, 'id'));
        if(auth()->check()){
            auth()->user()->tenant->transactionTemplateSplits()->save($transactionTemplateSplit);
        }
        else{
            $contact = \App\Models\Contact::withoutGlobalScopes()->where('id', array_get($fields, 'contact_id'))->first();
            array_set($transactionTemplateSplit, 'tenant_id', array_get($contact, 'tenant_id'));
            $transactionTemplateSplit->save();
        }
        
        if (array_has($fields, 'tags')) {
            $transactionTemplateSplit->tags()->sync($fields['tags']);
        }

        return $transactionTemplateSplit;
    }

    public function transactionPaymentOption($fields) {
        $payment = new PaymentOption();
        $category = array_get($fields, 'category');

        array_set($payment, 'category', $category);
        array_set($payment, 'contact_id', array_get($fields, 'contact_id'));

        if(in_array($category, ['check', 'ach'])){
            array_set($payment, 'last_four', array_get($fields, 'last_four'));
        }
        else if (in_array($category, ['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'])) {
            array_set($payment, 'first_four', null);
            array_set($payment, 'last_four', null);
        }
        else if($category === 'cc'){
            if(array_get($fields, 'payment_option_id') !== '0'){
                return array_get($fields, 'payment_option_id');
            }
            array_set($payment, 'card_type', array_get($fields, 'card_type'));
            $cardnumber = array_get($fields, 'first_four').'****'.array_get($fields, 'last_four');
            array_set($payment, 'card_number', $cardnumber);
            array_set($payment, 'first_four', array_get($fields, 'first_four'));
            array_set($payment, 'last_four', array_get($fields, 'last_four'));

        }
        else{
            return null;
        }

        auth()->user()->tenant->paymentOptions()->save($payment);

        $payment->createAltId(sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain')) );
        return array_get($payment, 'id');
    }

    
    /**
     * Stores autoTags for transactions
     * @param  array $fields 
     * @return array    ids that were added to contact
     */
    public function storeAutoTags($fields) {
        $tag_ids = [];
        $contact = Contact::find(array_get($fields,'contact_id'));
        if (!$contact) return null;
        
        $purpose = Purpose::find(array_get($fields, 'purpose_id'));
        if ($purpose && $purpose->id != 1) {
            if ($purpose->autoTag) $tag_ids[] = $purpose->autoTag->id;
            
            $parent_purpose = $purpose->parentPurpose;
            if ($parent_purpose && $parent_purpose->autoTag) $tag_ids[] = $parent_purpose->autoTag->id;
        } 
        
        $campaign = Campaign::find(array_get($fields, 'campaign_id'));
        if ($campaign && $campaign->id != 1) {
            if ($campaign->autoTag) $tag_ids[] = $campaign->autoTag->id;
        }
        
        $attached_tag_ids = $contact->tags()->whereIn('tag_id', $tag_ids)->pluck('tag_id')->toArray();
        $tag_ids = array_diff($tag_ids, $attached_tag_ids);
        $contact->tags()->attach($tag_ids);
        
        return $tag_ids;
    }
    
    
    /**
     * @param  array $fields
     * @param  array|TransactionTemplate $transactionTemplate 
     * @return Transaction
     */
    public function storeTransaction($fields, $transactionTemplate) {
        if (array_get($fields, 'deposit_date')) {
            array_set($fields, 'deposit_date', substr(array_get($fields, 'deposit_date'), 0, 10));
        }
        
        $transaction = mapModel(new Transaction(), $fields);
        $category = array_get($fields, 'category');
        
        $this->storeAutoTags($fields);

        if( $category === 'check' ){
            if( array_get($fields, 'payment_option_id', 0) <= 0 ){
                $paymentOption = PaymentOption::where([
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['category', '=', 'check'],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption)){
                    $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type'));
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));
            }
        }

        if (in_array($category, ['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'])) {
            $paymentOption = PaymentOption::where([
                ['category', '=', $category],
                ['contact_id', '=', array_get($fields, 'contact_id')]
            ])->first();

            if(is_null($paymentOption) ){
                $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type', 'first_four', 'last_four'));
                auth()->user()->tenant->paymentOptions()->save($paymentOption);
            }

            array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
        }
        if( $category === 'ach' ){
            if(array_get($fields, 'payment_option_id') == '0'){
                $paymentOption = PaymentOption::where([
                    ['category', '=', 'ach'],
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption) ){
                    $paymentOption = mapModel(new PaymentOption(), array_except($fields, 'card_type', 'first_four'));
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));
            }
        }
        if( $category === 'cc' ){
            if(array_get($fields, 'payment_option_id') == '0'){
                $paymentOption = PaymentOption::where([
                    ['category', '=', 'cc'],
                    ['contact_id', '=', array_get($fields, 'contact_id')],
                    ['first_four', '=', array_get($fields, 'first_four')],
                    ['last_four', '=', array_get($fields, 'last_four')]
                ])->first();

                if(is_null($paymentOption) ){
                    $paymentOption = mapModel(new PaymentOption(), $fields);
                    auth()->user()->tenant->paymentOptions()->save($paymentOption);
                }
                array_set($transaction, 'payment_option_id', array_get($paymentOption, 'id'));
            }
            else{
                array_set($transaction, 'payment_option_id', array_get($fields, 'payment_option_id'));
            }
        }

        array_set($transaction, 'transaction_template_id', array_get($transactionTemplate, 'id'));
        array_set($transaction, 'transaction_last_updated_at', Carbon::now());
        //i can't remember why a recurring transaction should have status = stub
        /*
        if( (bool) array_get($transactionTemplate, 'is_recurring', false) ){
            array_set($transaction, 'transaction_initiated_at', array_get($fields, 'billing_start_date'));
            array_set($transaction, 'status', 'stub');
        }
        else if( (bool) array_get($transactionTemplate, 'is_pledge', false) ){
            array_set($transaction, 'status', 'pending');
        }
        else{
            array_set($transaction, 'status', $this->default_status);
        }
        */
        if( (bool) array_get($transactionTemplate, 'is_pledge', false) ){
            array_set($transaction, 'status', 'pending');
        }
        else{
            array_set($transaction, 'status', $this->default_status);
        }

        if(array_get($transactionTemplate, 'is_pledge')){
            array_set($transaction, 'channel', 'unknown');
        }

        $transaction_initiated_at = Carbon::parse(array_get($fields, 'transaction_initiated_at'));
        array_set($transaction, 'transaction_initiated_at', $transaction_initiated_at);


        auth()->user()->tenant->transactions()->save($transaction);

        return $transaction;
    }

    /**
     * @param  array $fields      
     * @param  array|Transaction $transaction 
     * @return TransactionSplit
     */
    public function storeTransactionSplit($fields, $transaction) {
        $transactionSplit = mapModel(new TransactionSplit(), $fields);
        array_set($transactionSplit, 'transaction_id', array_get($transaction, 'id'));

        auth()->user()->tenant->transactionSplits()->save($transactionSplit);
        if (array_has($fields, 'tags')) {
            $transactionSplit->tags()->sync($fields['tags']);
        }
        return $transactionSplit;
    }
    
    public function storeSoftCredits($fields, Transaction $parentTransaction)
    {
        foreach (array_get($fields, 'softCredits') as $softCredit) {
            array_set($fields, 'contact_id', array_get($softCredit, 'contact_id'));
            array_set($fields, 'amount', array_get($softCredit, 'amount'));
            array_set($fields, 'tax_deductible', 0);
            //array_set($fields, 'type', 'soft_credit'); // TODO check if we can do that and it does not break the code if we change the type
            array_set($fields, 'tags', null);
            array_set($fields, 'parent_transaction_id', array_get($parentTransaction, 'id'));
            array_set($fields, 'category', 'soft_credit');
            array_set($fields, 'check_number', null);
            array_set($fields, 'comment', null);
            array_set($fields, 'card_type', null);
            array_set($fields, 'first_four', null);
            array_set($fields, 'last_four', null);
            array_set($fields, 'payment_option_id', null);
            
            if (array_get($softCredit, 'remove', false)) {
                $this->deleteSoftCredit($softCredit);
                continue;
            }
            
            if (array_get($softCredit, 'update', false)) {
                $this->updateSoftCredit($softCredit, $fields);
                continue;
            }
            
            if (!array_get($softCredit, 'isTemp')) {
                continue;
            }
            
            $transactionTemplate = $this->storeTransactionTemplate($fields);
            $transactionTemplateSplit = $this->storeTransactionTemplateSplit($fields, $transactionTemplate);
            $transaction = $this->storeTransaction($fields, $transactionTemplate);
            array_set($fields, 'transaction_template_split_id', array_get($transactionTemplateSplit, 'id'));
            $this->storeTransactionSplit($fields, $transaction);
        }
    }
    
    public function updateSoftCredit($softCredit, $fields)
    {
        $transaction = Transaction::find(array_get($softCredit, 'soft_credit_id'));
        $split = $transaction->splits[0];
        
        $transactionTemplate = $this->updateTransactionTemplate(array_get($transaction, 'template'), $fields);
        $transaction = $this->updateTransaction($transaction, $fields, $transactionTemplate);
        $transactionTemplateSplit = $this->updateTransactionTemplateSplit($split->transactionTemplateSplit, $fields, $transactionTemplate);
        $transactionSplit = $this->updateTransactionSplit($split, $fields, $transaction);
    }
    
    public function deleteSoftCredit($softCredit)
    {
        $transaction = Transaction::find(array_get($softCredit, 'soft_credit_id'));
        $split = $transaction->splits[0];
        
        foreach ($split->transaction->template->splits as $s) {
            $s->delete();
        }
        $split->transaction->template->delete();
        $split->transaction->delete();
        $split->delete();
    }
    
    public function storeAttachments($fields, Transaction $transaction)
    {
        foreach (array_get($fields, 'attachments') as $attachment) {
            $document = Document::where('uuid', array_get($attachment, 'attachment_id'))->first();
            
            if ($document && array_get($attachment, 'remove')) {
                if (env('AWS_ENABLED')) {
                    Storage::disk('s3')->delete(array_get($document, 'path'));
                } else {
                    checkAndDeleteFile(array_get($document, 'path'));
                }
                
                $document->delete();
                
                continue;
            }
            
            if (!array_get($attachment, 'isTemp')) {
                continue;
            }
            
            if ($document) {
                array_set($document, 'relation_id', array_get($transaction, 'id'));
                array_set($document, 'relation_type', get_class($transaction));
                array_set($document, 'is_temporary', 0);
                $document->update();
            }
        }
    }
}
