<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPInvoiceDetail extends BaseModel
{
    protected $table = 'mp_invoice_details';
    
    
    public function scopeForCRM($query)
    {
        $query->where('description','like', 'Church%Management:%');
    }
    
    public function scopeForAccounting($query)
    {
        $query->where('description','like', '%Accounting:%');
    }
}
