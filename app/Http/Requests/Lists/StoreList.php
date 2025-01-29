<?php

namespace App\Http\Requests\Lists;

use App\Models\Lists;
use Illuminate\Foundation\Http\FormRequest;

class StoreList extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canDo('create',Lists::class);
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
            'permission_reminder' => 'required',
            /*
            'company' => 'required',
            'mailing_address_1' => 'required',
            'city' => 'required',
            'region' => 'required',
            'postal_code' => 'required',
            'country_id' => 'required',
            'from_name' => 'required',
            'from_email' => 'required',
            'subject' => 'required',
            'language' => 'required',
             * 
             */
        ];
    }
}
