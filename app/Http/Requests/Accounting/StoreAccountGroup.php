<?php

namespace App\Http\Requests\Accounting;

class StoreAccountGroup extends Store
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group.name' => 'required|max:150',
            'group.chart_of_account' => 'required'
        ];
    }
    
    public function attributes()
    {
        return [
            'group.name' => 'name',
            'group.chart_of_account' => 'required'
        ];
    }
}
