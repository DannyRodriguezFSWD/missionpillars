<?php

namespace App\Http\Requests\Contacts;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreContact extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return auth()->user()->canDo('create',Contact::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'first_name' => 'required_if:type,person|max:45',
            'last_name' => 'required_if:type,person|max:45',
            'organization_name' => 'required_if:type,organization|max:45',
            'email_1' => [
                'nullable',
                'email',
                Rule::unique('contacts')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id)->whereNull('deleted_at');
                })
            ],
            'family_position' => 'required_with:family_id',
            'family_id' => 'required_with:family_position'
        ];
    }
    
    public function messages() 
    {
        return [
            'email_1.unique' => __('Email 1 must be unique.  You can consider using email 2 as that does not need to be unique. Also, ask yourself, why do you have two contacts with the same email address?'),
            'family_id.required_with' => 'You have selected a family position but no family has been added for the contact.',
            'family_position.required_with' => 'Please select a family position.'
        ];
    }
}
