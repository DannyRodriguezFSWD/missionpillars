<?php

namespace App\Http\Requests\Events;

use App\Models\CalendarEvent;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvent extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canDo('create', CalendarEvent::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_id' => 'required',
            'name' => 'required',
            'content' => 'required',
            'end_date' => ['custom_end_date']
        ];
    }
    
    public function messages() 
    {
        return [
            'contact_id.required' => __('Please select a valid manager.'),
            'content.required' => __('The description field is required.')
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->addExtension('custom_end_date', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            if (!array_get($data, 'is_all_day') && array_get($data, 'start_date') && array_get($data, 'end_date')) {
                $start = array_get($data, 'start_date').' '.array_get($data, 'start_time');
                $end = array_get($data, 'end_date').' '.array_get($data, 'end_time');
                return strtotime($start) <= strtotime($end);
            } else {
                return true;
            }
        });
        
        $validator->addReplacer('custom_end_date', function ($message, $attribute, $rule, $parameters, $validator) {
            return __("The end date cannot be earlier than the start date.", compact('attribute'));
        });
    }
}
