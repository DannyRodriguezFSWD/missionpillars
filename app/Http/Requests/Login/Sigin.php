<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class Sigin extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'subdomain' => 'required',
        ];
    }

    public function messages() {
        return [
            'email.required' => __("Your email/password combination is not correct"),
            'email.email' => __("The email must be a valid email address."),
            'subdomain' => __("This subdomain doesn't match our records")
        ];
    }

}
