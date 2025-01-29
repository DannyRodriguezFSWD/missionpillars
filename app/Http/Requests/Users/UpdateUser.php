<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUser extends FormRequest
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Routing\Route|mixed|object|string|null
     */
    public $user_;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->user_ = User::findOrFail($this->user);
        return auth()->user()->canDo('update', $this->user_);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|max:190',
            'last_name' => 'required|max:200',
            'uid' => 'required',
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id)->whereNull('deleted_at');
                })->ignore($this->user)
            ]
        ];
        
        if (auth()->user()->can('change-role')) $rules['role'] = 'required|min:1|integer';
        if ($this->password != null) $rules['password'] = 'required|min:6|confirmed';

        return $rules;
    }

    public function messages() 
    {
        return [
            'email.unique' => __('Another user has this email and emails must be unique.')
        ];
    }
}
