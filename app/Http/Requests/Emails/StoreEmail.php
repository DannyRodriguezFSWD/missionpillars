<?php

namespace App\Http\Requests\Emails;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmail extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $this->sanitize();
        return [
            'content' => 'required'
        ];
    }

    public function sanitize() {
        $input = $this->all();

        $input['content'] = filter_var($input['content'], FILTER_SANITIZE_STRING);

        $this->replace($input);
    }

}
