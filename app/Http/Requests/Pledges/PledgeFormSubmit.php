<?php

namespace App\Http\Requests\Pledges;

use Illuminate\Foundation\Http\FormRequest;

class PledgeFormSubmit extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // this is a public form submission
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
            'contact_id' => 'required|integer'
        ];
    }
    
    public function messages() {
        return [
            'contact_id.required' => 'Selecte a registered contact',
            'contact_id.integer' => 'Selecte a registered contact'
        ];
    }
}
