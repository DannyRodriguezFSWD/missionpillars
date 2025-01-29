<?php

namespace App\Http\Requests\CustomFields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSection extends FormRequest {

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
            'name' => [
                'required',
                'max:150',
                Rule::unique('custom_field_sections')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                })->ignore($this->custom_field_section)
            ]
        ];
    }
    
    public function messages() 
    {
        return [
            'name.required' => __('The name field is reuired.'),
            'name.unique' => __('There is already a section with this name.')
        ];
    }
}
