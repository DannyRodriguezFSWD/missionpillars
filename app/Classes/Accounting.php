<?php

namespace App\Classes;

use App\Models\Account;
use App\Models\Register;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Models\RegisterSplit;

/**
 * Description of ApiUnauthorizedToken
 *
 * @author josemiguel
 */
class Accounting {

    public static function createOrUpdateRegistry($values, $record_type){
        try {
            $register_id = Crypt::decrypt(array_get($values, 'id'));
        } catch (\Throwable $th) {
            $register_id = array_get($values, 'id');
        }

        try {
            $account_register_id = Crypt::decrypt(array_get($values, 'account_register_id'));
        } catch (\Throwable $th) {
            $account_register_id = array_get($values, 'account_register_id');
        }
        
        if(!is_null($register_id)){
            $register = Register::findOrFail($register_id);
            array_set($register, 'credit', null);
            array_set($register, 'debit', null);
            array_set($register, 'amount', null);
        }
        else{
            $register = new Register();
        }
        
        $registry_amount = self::getPositiveOrNegativeAmount($account_register_id, $record_type, array_get($values, 'amount', 0));
        $date = Carbon::parse(array_get($values, 'date'))->toDateString();

        mapModel($register, $values);
        if(!empty($account_register_id)){
            array_set($register, 'account_register_id', $account_register_id);
        }
        array_set($register, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
        array_set($register, 'date', $date);
        array_set($register, 'amount', $registry_amount);
        
        if (is_null($register_id)) {
            if(in_array(array_get($values, 'register_type'), ['journal_entry', 'fund_transfer'])){
                $journal_entry_id = Register::nextJournalEntryId();
                array_set($register, 'journal_entry_id', $journal_entry_id);
            }
            $register->save();
        }  
        else {
            $register->update();
        }

        return $register;
    }

    public static function createOrUpdateSplits($register, $splits, $record_type, $splits_records_type){
        $result = [];
        foreach ($splits as $index => $split) {
            try {
                $fund_id = Crypt::decrypt(array_get($split, 'fund_id'));
            } catch (\Throwable $th) {
                $fund_id = array_get($split, 'fund_id');
            }

            try {
                $contact_id = Crypt::decrypt(array_get($split, 'contact_id'));
            } catch (\Throwable $th) {
                $contact_id = array_get($split, 'contact_id');
            }

            try {
                $account_id = Crypt::decrypt(array_get($split, 'account_id'));
            } catch (\Throwable $th) {
                $account_id = array_get($split, 'account_id');
            }

            try {
                $split_id = Crypt::decrypt(array_get($split, 'id'));
            } catch (\Throwable $th) {
                $split_id = array_get($split, 'id');
            }

            $transaction_split_id = array_get($split, 'transaction_split_id', 0);

            if(in_array(array_get($register, 'register_type'), ['journal_entry', 'fund_transfer'])){
                $record_type = null;
                if(!empty(array_get($split, 'credit'))){
                    $record_type = 'credit';
                }
                
                if(!empty(array_get($split, 'debit'))){
                    $record_type = 'debit';
                }
                
                if(!empty($split_id)){
                    $journal_entry_split = RegisterSplit::findOrFail($split_id);
                }
                else{
                    $journal_entry_split = new RegisterSplit();
                }
                
                $split_amount = self::getPositiveOrNegativeAmount($account_id, $record_type, array_get($split, 'amount', 0));
                $journal_entry_split = self::createOrUpdateSplitRecord($journal_entry_split, $split, $register, $record_type, $fund_id, $contact_id, $split_amount, $account_id);
                if($transaction_split_id > 0){
                    $journal_entry_split->transactions()->sync($transaction_split_id);
                }

                if($index == 0){
                    array_set($register, $record_type, array_get($split, 'amount', 0));
                    array_set($register, 'amount', $split_amount);
                    $register->update();
                }

                if($index % 2 == 1){//we have a pair
                    $previous_split = array_get($result, 0);
                    if(!is_null($previous_split)){
                        array_set($journal_entry_split, 'splits_partner_id', array_get($previous_split, 'id'));
                        $journal_entry_split->update();

                        array_set($previous_split, 'splits_partner_id', array_get($journal_entry_split, 'id'));
                        $previous_split->update();
                    }
                }

                array_push($result, $journal_entry_split);
            }
            else if(array_get($register, 'register_type') == 'fund_transfer___'){
                
            }
            else{
                $account_register_id = array_get($register, 'account_register_id');
                
                if(!empty($split_id)){
                    $split_pair = RegisterSplit::findOrFail($split_id);
                    $split_auto = RegisterSplit::findOrFail(array_get($split_pair, 'splits_partner_id'));
                }
                else{
                    $split_pair = new RegisterSplit();
                    $split_auto = new RegisterSplit();
                }

                $auto_split_amount = self::getPositiveOrNegativeAmount($account_register_id, $record_type, array_get($split, 'amount', 0));
                $split_auto = self::createOrUpdateSplitRecord($split_auto, $split, $register, $record_type, $fund_id, $contact_id, $auto_split_amount, array_get($register, 'account_register_id'));
                if($transaction_split_id > 0){
                    $split_auto->transactions()->sync($transaction_split_id);
                }

                $entered_split_amount = self::getPositiveOrNegativeAmount($account_id, $splits_records_type, array_get($split, 'amount', 0));
                $split_pair = self::createOrUpdateSplitRecord($split_pair, $split, $register, $splits_records_type, $fund_id, $contact_id, $entered_split_amount, $account_id);
                if($transaction_split_id > 0){
                    $split_pair->transactions()->sync($transaction_split_id);
                }

                array_set($split_pair, 'splits_partner_id', array_get($split_auto, 'id'));
                array_set($split_auto, 'splits_partner_id', array_get($split_pair, 'id'));
                $split_pair->update();
                $split_auto->update();

                array_push($result, $split_pair);
                array_push($result, $split_auto);
            }

        }

        return $result;
    }

    /**
     * @var RegisterSplit $record
     * @var Array $split
     * @var Register $register
     * @var String $record_type credit|debit
     * @var Integer $fund_id
     * @var Integer $contact_id
     * @var Decimal $split_amount
     */
    public static function createOrUpdateSplitRecord($record, $split, $register, $record_type, $fund_id, $contact_id, $split_amount, $account_id){
        try {
            //reset values, this is useful on updating splits
            array_set($record, 'amount', null);
            array_set($record, 'credit', null);
            array_set($record, 'debit', null);
            mapModel($record, array_except($split, ['credit', 'debit']));
            array_set($record, $record_type, array_get($split, 'amount'));
            array_set($record, 'tenant_id', array_get($register, 'tenant_id'));
            array_set($record, 'register_id', array_get($register, 'id'));
            array_set($record, 'fund_id', $fund_id);
            array_set($record, 'contact_id', $contact_id);
            array_set($record, 'account_id', $account_id);
            array_set($record, 'amount', $split_amount);
            
            if (array_get($split, 'fee', 0) > 0) {
                array_set($record, 'amount', $split_amount - array_get($split, 'fee', 0));
                array_set($record, $record_type, array_get($split, 'amount') - array_get($split, 'fee', 0));
            }
            
            $record->save();
            
        } catch (\Throwable $th) {
            throw $th;
            //dd($record, $split, $record_type);
        }
        
        return $record;
    }
    
    public static function getPositiveOrNegativeAmount($account_id, $movement, $amount){
        $account = Account::find($account_id);
        $chart_of_account = array_get($account, 'group.chart_of_account');
        if($movement == 'credit'){
            if(in_array($chart_of_account, ['asset', 'expense'])){//decrease
                //negative
                $amount = abs($amount) * -1;
            }
            else if(in_array($chart_of_account, ['liability', 'equity', 'income'])){//increase
                //positive
                $amount = abs($amount);
            }
        }
        else{//debit
            if(in_array($chart_of_account, ['asset', 'expense'])){//increase
                //positive
                $amount = abs($amount);
            }
            else if(in_array($chart_of_account, ['liability', 'equity', 'income'])){//decrease
                //negative
                $amount = abs($amount) * -1;
            }
        }

        return $amount;
    }

    public static function getCreditOrDebitTitles($account_id){
        $account = Account::find($account_id);
        $chart_of_account = array_get($account, 'group.chart_of_account');
        $titles = [
            'debit' => 'Debit',
            'credit' => 'Credit',
        ];

        if(in_array($chart_of_account, ['asset'])){
            $titles = [
                'debit' => 'Deposit',
                'credit' => 'Payment',
            ];
        }
        else if(in_array($chart_of_account, ['liability'])){
            $titles = [
                'debit' => 'Payment',
                'credit' => 'Charge',
            ];
        }

        return $titles;
    }
    
}
