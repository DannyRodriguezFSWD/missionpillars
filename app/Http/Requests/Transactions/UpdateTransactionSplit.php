<?php

namespace App\Http\Requests\Transactions;

class UpdateTransactionSplit extends Store
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('transaction-update');
    }
    
    
    // Inherits Store::rules
}
