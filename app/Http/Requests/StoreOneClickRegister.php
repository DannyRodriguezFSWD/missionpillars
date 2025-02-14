<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOneClickRegister extends FormRequest {

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
//            'name' => 'required|max:255',
//            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255',
//            'password' => 'required|min:6|confirmed',
            'subdomain' => 'required',
        ];
    }

}
