<?php

namespace App\Http\Requests\Accounting;

class StoreAccount extends Store
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account.name' => 'required|max:150',
            'account.number' => 'required',
            'account.account_group_id' => 'required'
        ];
    }
    
    public function attributes()
    {
        return [
            'account.name' => 'name',
            'account.number' => 'account number',
            'account.account_group_id' => 'account group'
        ];
    }
}
