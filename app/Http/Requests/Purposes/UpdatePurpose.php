<?php

namespace App\Http\Requests\Purposes;

use App\Models\Purpose;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurpose extends FormRequest
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Routing\Route|mixed|object|string|null
     */
    public $purpose_;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->purpose_ = Purpose::findOrFail($this->purpose);
        return auth()->user()->canDo('update', $this->purpose_);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->purpose_ = Purpose::findOrFail($this->purpose);

        $rules = [
            'name' => 'required|max:150',
        ];

        if ($this->purpose_->createdFromC2G() === false) $rules['description'] = 'required';

        if (!is_numeric($this->contact_id)) $this->merge(['contact_id' => null]);
        if (!is_numeric($this->account_id)) $this->merge(['account_id' => null]);
        if (!is_numeric($this->fund_id)) $this->merge(['fund_id' => null]);

        if ($this->type == 'Missionary') $rules['contact_id'] = 'required|integer';
        return $rules;
    }

    public function messages()
    {
        return [
            'contact_id.integer' => __('Select full suggested contact'),
            'contact_id.required' => __('Select full suggested contact'),
        ];
    }
}
