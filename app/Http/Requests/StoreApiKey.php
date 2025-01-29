<?php

namespace App\Http\Requests;

use App\Models\OauthAccessToken;
use Illuminate\Foundation\Http\FormRequest;

class StoreApiKey extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canDo('create',OauthAccessToken::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required'
        ];
    }
}
