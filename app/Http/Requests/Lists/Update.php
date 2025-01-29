<?php

namespace App\Http\Requests\Lists;

use App\Models\Lists;
use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * @var \Illuminate\Routing\Route|mixed|object|string
     */
    public $list_;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->list_ = Lists::findOrFail($this->list);
        return auth()->user()->canDo('update',$this->list_);
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
        ];
    }
}
