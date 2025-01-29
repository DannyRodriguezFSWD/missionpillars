<?php

namespace App\Http\Requests\Emails;

use Illuminate\Foundation\Http\FormRequest;

class CountNumberEmails extends FormRequest
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
            'send_number_of_emails' => 'required_without:send_to_all',
            'do_not_send_within_number_of_days' => 'required'
        ];
    }
    
    public function messages() {
        return [
            'send_number_of_emails.required_without' => 'Enter a valid number >= 0 or check "All"',
            'do_not_send_within_number_of_days.required' => 'Enter a valid number >= 0'
        ];
    }
}
