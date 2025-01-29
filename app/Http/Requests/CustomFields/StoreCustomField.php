<?php

namespace App\Http\Requests\CustomFields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomField extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() 
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() 
    {
        return [
            'type' => 'required',
            'name' => [
                'required',
                'max:150',
                Rule::unique('custom_fields')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id)->whereNull('deleted_at');
                })
            ],
            'pick_list_values' => 'required_if:type,select,multiselect'
        ];
    }
    
    public function messages() 
    {
        return [
            'name.required' => __('The label field is reuired.'),
            'name.unique' => __('There is already a field with this label.')
        ];
    }
}
