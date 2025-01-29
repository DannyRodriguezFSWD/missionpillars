<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'addresses';
    
    public function contact() {
        return $this->morphTo('relation');
    }
    
    public function group() {
        return $this->morphTo('relation');
    }
    
    public function countries() {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
    
    
    /*** Scopes ***/
    
    /**
     * Scopes by mailing address
     *
     * NOTE requiring country and or country_id may come back, perhaps as a different scope with regard to international addresses
     * @param  Builder $query    
     * @param  array  $criteria Optional. Defaults to ['is_mailing'=>true,'mailing_address_1','city','region','postal_code']
     * @return [type]           [description]
     */
    public function scopeMailing($query, $criteria = ['is_mailing'=>true,'mailing_address_1','city','region','postal_code']) {
        foreach ($criteria as $key => $value) {
            if (is_numeric($key)) {
                $query->whereNotNull($value);
            } else {
                $query->where($key, $value);
            }
        }
        /*
        $query->where('is_mailing', true)
        ->whereNotNull('mailing_address_1')
        ->whereNotNull('city')
        ->whereNotNull('region')
        // ->where(function($query){
        //     $query->whereNotNull('country')->orWhereNotNull('country_id');
        // })
        ->whereNotNull('postal_code');
        */
        
        return $query;
    }
}
