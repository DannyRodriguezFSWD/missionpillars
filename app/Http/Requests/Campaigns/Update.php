<?php

namespace App\Http\Requests\Campaigns;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Routing\Route|mixed|object|string|null
     */
    public $campaign_;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->campaign_ = Campaign::findOrFail($this->campaign);
        return auth()->user()->canDo('update', $this->campaign_);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (!is_numeric($this->contact_id)) $this->merge(['contact_id' => null]);
        return [
            'contact_id' => 'required_if:page_type,==,Missionary',
        ];
    }

    public function messages()
    {
        return [
            'contact_id.required_if' => __('Select full suggested contact')
        ];
    }
}
