<?php

namespace App\Http\Requests\Statements;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatement extends FormRequest
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
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'statement' => 'required'
        ];
    }
}
