<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Promocode extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'reward',
        'quantity',
        'expiry_date',
    ];
    
    /** Scopes **/
    
    public function scopeCode($query, $code) 
    {
        return $query->where('code',$code);
    }
    
    public function scopeCreatedToday($query)
    {
        return $query->whereBetween('created_at',[
            Carbon::today()->startOfDay(),
            Carbon::today()->endOfDay()
        ]);
    }
    
    public function scopeLimited($query) 
    {
        return $query->where('quantity', '>=', 0);
    }
    
    public function scopeUnlimited($query) 
    {
        return $query->where('quantity',-1);
    }
    
    public function scopeValid($query)
    {
        return $query->where('expiry_date', '>=', Carbon::today())
        ->where('quantity','>=',-1)
        ->where('quantity','!=',0);
    }
}
