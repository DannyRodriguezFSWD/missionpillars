<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransaction extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_id' => 'required|integer',
            'amount' => 'required|numeric',
            'purpose_id' => 'required|integer|min:1',
            'fee' => 'required|numeric',
        ];
    }
    
    public function messages() {
        return [
            'contact_id.required' => __('Select full suggested contact'),
            'contact_id.integer' => __('Select full suggested contact'),
            'purpose_id.min' => __('Select Purpose')
        ];
    }
}
