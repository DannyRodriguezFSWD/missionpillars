<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MPInvoice extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'mp_invoices';
    

    
    /** relationships **/
    
    public function details(){
        return $this->hasMany(MPInvoiceDetail::class, 'mp_invoice_id');
    }
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
    
    
    /** Scopes **/
    
    public function scopePaid($query)
    {
        $query->whereNotNull('paid_at')->where('total_amount','>',0);
        return $query;
    }
    
    public function scopeUnpaid($query)
    {
        $query->whereNull('paid_at')->where('total_amount','>',0);
        return $query;
    }
    
    public function scopeForCRM($query)
    {
        $query->whereHas('details', function($d) {
            $d->forCRM();
        });
    }
    
    public function scopeForAccounting($query)
    {
        $query->whereHas('details', function($d) {
            $d->forAccounting();
        });
    }
}
