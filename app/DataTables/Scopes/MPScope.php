<?php

namespace App\DataTables\Scopes;

use Yajra\Datatables\Contracts\DataTableScopeContract;

abstract class MPScope implements DataTableScopeContract
{
    protected $request;
    
    public function __construct($request = null) 
    {
        $this->request = $request ?: request();
    }
}
