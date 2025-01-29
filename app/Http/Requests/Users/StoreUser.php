<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUser extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->canDo('create', User::class);
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
            'password' => 'required|min:6|confirmed',
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id)->whereNull('deleted_at');
                })
            ]
        ];
        if (auth()->user()->can('role-change')) $rules['role'] = 'required|min:1|integer';
        return $rules;
    }

    public function messages()
    {
        return [
            'role.integer' => __('Select a role'),
            'email.unique' => __('Another user has this email and emails must be unique.')
        ];
    }

}
