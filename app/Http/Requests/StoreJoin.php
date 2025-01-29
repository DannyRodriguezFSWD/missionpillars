<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\Subdomains;

class StoreJoin extends FormRequest {

    use Subdomains;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        $url = \Illuminate\Support\Facades\Request::getHost();
        $subdomain = $this->getSubdomain($url);
        $tenant = $this->getTenant($subdomain);
        return $tenant ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email_1' => 'required|max:255',
        ];
    }

}
