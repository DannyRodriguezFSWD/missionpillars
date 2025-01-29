<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest {

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
        if ((string) $this->isSubdomain === '1') {
            return [
                'name' => 'required|max:190',
                'lastname' => 'required|max:200',
                'email' => 'required|email|max:190',
                'password' => 'required|min:6|confirmed',
            ];
        }

        return [
            'name' => 'required|max:190',
            'lastname' => 'required|max:200',
            'email' => 'required|email|max:190',
            'subdomain' => 'required|string|max:50|unique:tenants',
            'password' => 'required|min:6|confirmed',
            'organization' => 'required|max:200',
            'phone' => 'required|max:50',
        ];
    }

}
