<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;

use App\MPLog;

class BankTransactionsController extends Controller {
    
    public function update(Request $request, $id) {
        $banktransaction = BankTransaction::find($id);
        if (!$banktransaction) abort(400);
        $banktransaction->fill($request->all());
        $banktransaction->save();
        
        return response()->json($banktransaction->toArray());
    }
}
