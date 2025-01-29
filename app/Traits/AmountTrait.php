<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\AccountGroup;

/**
 *
 * @author josemiguel
 */
trait AmountTrait {

    private function getAccountGroupByAccountId($account_id) {
        $group_id = Account::where('id', $account_id)->value('account_group_id');
        $account_group = AccountGroup::where('id', $group_id)->value('chart_of_account');
        return $account_group;
    }

    private function getFundAccountIdByFundId($fund_id) {
        $account_id = Account::where('account_fund_id', $fund_id)->value('id');
        return $account_id;
    }

    private function getAutoAmountByAccountId($amount, $amount_type, $aol, &$split_auto) {

        if ($amount_type == 'credit') {
            array_set($split_auto, 'credit', $amount);
            if($aol == 'asset') {
                $amount = $amount * -1;
            }
        } else {
            array_set($split_auto, 'debit', $amount);
            if($aol == 'liability') {
                $amount = $amount * -1;
            }
        }
        return $amount;
    }

    private function getEnteredAmountByAccountId($account_id, $amount, $amount_type) {

        $account_group = $this->getAccountGroupByAccountId($account_id);

        switch ($account_group) {
            case 'asset' :
            case 'expense' :
                if($amount_type == 'debit') {
                    $amount = $amount * -1;
                }
                break;
            case 'liability' :
            case 'equity' :
            case 'income' :
                if($amount_type == 'credit') {
                    $amount = $amount * -1;
                }
                break;
        }
        return $amount;
    }

    private function getAmountTypeByAccountId($account_id, $amount) {

        $account_group = $this->getAccountGroupByAccountId($account_id);
        $amountType = 'credit';

        switch ($account_group) {
            case 'asset' :
            case 'expense' :
                if($amount > 0) {
                    $amountType = 'debit';
                }
                break;
            case 'liability' :
            case 'equity' :
            case 'income' :
                if($amount < 0) {
                    $amountType = 'debit';
                }
                break;
        }
        return $amountType;
    }
}
