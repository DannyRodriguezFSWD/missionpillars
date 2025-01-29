<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinJoin extends FormRequest
{
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
            'id' => 'required|integer',
        ];
    }
    
    public function messages() {
        return [
            'id.required' => __('Select full suggested contact or subscribe'),
            'id.integer' => __('Select full suggested contact or subscribe'),
        ];
    }
}
