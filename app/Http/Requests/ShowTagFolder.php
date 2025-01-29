<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;

class ShowTagFolder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $params = array_values($this->route()->parameters());
        $folder = Folder::where([
            ['id', '=', $params[0]],
            ['type', '=', 'TAGS']
        ])->first();
        if(!$folder || $folder->tenant_id !== Auth::user()->tenant_id && $folder->tenant_id !== null){
            abort(404);
        }
        return Auth::check() && $folder->tenant_id === Auth::user()->tenant_id || $folder->tenant_id === null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
