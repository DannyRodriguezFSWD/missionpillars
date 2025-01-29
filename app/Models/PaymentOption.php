<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AlternativeIdTrait;
use App\Traits\CardPaymentsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentOption extends BaseModel
{
    use SoftDeletes, AlternativeIdTrait, CardPaymentsTrait;
    
    public function scopeStripe($query) 
    {
        return $query->where('category', 'cc')->whereNotNull('card_id');
    }
    
    
    public function getFulLAccountNumberAttribute()
    {
        return str_pad($this->last_four, 12, '*', STR_PAD_LEFT);
    }
}
