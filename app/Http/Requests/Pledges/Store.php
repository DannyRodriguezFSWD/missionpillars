<?php

namespace App\Http\Requests\Pledges;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('pledge-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'contact_id' => 'required|integer',
            'amount' => 'required|numeric',
            'purpose_id' => 'required|integer|min:1',
            'billing_cycles' => 'required_if:is_recurring,==,1',
            'billing_period' => 'required_if:is_recurring,==,1|string',
            'billing_start_date' => 'required_if:is_recurring,==,1',
        ];
        
        if(in_array(array_get($this->request->all(), 'category'), ['cc', 'ach'])){
            array_set($rules, 'first_four', 'required_if:payment_option_id,==,0');
            array_set($rules, 'last_four', 'required_if:payment_option_id,==,0');
            array_set($rules, 'payment_option_id', 'not_in:-1');
        }

        if(in_array(array_get($this->request->all(), 'category'), ['check'])){
            array_set($rules, 'payment_option_id', 'not_in:-1');
        }
        
        return $rules;
    }
    
    public function messages() {
        return [
            'contact_id.required' => __('Select full suggested contact'),
            'contact_id.integer' => __('Select full suggested contact'),
            'purpose_id.min' => __('Select Purpose'),
            'billing_cycles.required_if' => __('Enter a valid number'),
            'first_four.required_if' => __('Enter first four numbers'),
            'last_four.required_if' => __('Enter last four numbers'),
            'payment_option_id.not_in' => __('Select a valid payment option'),
        ];
    }
}
