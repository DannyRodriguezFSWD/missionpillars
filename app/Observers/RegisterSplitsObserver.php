<?php

namespace App\Observers;

use App\Constants;
use App\Models\RegisterSplit;
use Illuminate\Support\Facades\DB;

/**
 * Description
 *
 * @author josemiguel
 */
class RegisterSplitsObserver {
    
    public function created(RegisterSplit $split) {
    }
    
    public function updated(RegisterSplit $split) {
        //dd($split);
    }
    
    public function deleted(RegisterSplit $split) {
        //dd($split);
        DB::table('transactions_registers')
            ->where('register_split_id', array_get($split, 'id'))
            ->delete();
    }
}
