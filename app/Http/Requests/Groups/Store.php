<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @todo check if user can group-create
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('group-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'contact_id' => 'required'
        ];
    }
    
    public function messages() 
    {
        return [
            'contact_id.required' => 'Please choose a group leader'
        ];
    }
}
