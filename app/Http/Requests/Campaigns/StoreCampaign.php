<?php

namespace App\Http\Requests\Campaigns;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaign extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canDo('create',Campaign::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page_type' => 'required',
            'contact_id' => 'required_if:page_type,==,Missionary',
            'name' => 'required',
            'description' => 'required'
        ];
    }
    
    public function messages() {
        return [
            'contact_id.required_if' => __('Select full suggested contact')
        ];
    }
}
