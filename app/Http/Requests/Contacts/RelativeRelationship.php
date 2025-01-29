<?php

namespace App\Http\Requests\Contacts;

use Illuminate\Foundation\Http\FormRequest;

class RelativeRelationship extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'relative_id' => 'required|integer',
            'contact_relationship' => 'required',
            'relative_relationship' => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'relative_id.required' => __('Please choose a contact.'),
            'relative_id.integer' => __('Please choose a contact.')
        ];
    }
}
