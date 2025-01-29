<?php
namespace App\Classes\Commands\Pledges;

use App\Models\TransactionTemplate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
/**
 * Description of UpdatePrimisedPayDate
 *
 * @author josemiguel
 */
class UpdatePromisedPayDate {
    
    public function run($action = 'default', $args = []) {
        if( $action === 'default' ){
            $this->runUpdatePrimisedPayDate($args);
        }
    }
    
    private function runUpdatePrimisedPayDate($args) {
        $yesterday = Carbon::now()->subDay(1);
        $pledges = TransactionTemplate::withoutGlobalScopes()->where([
            ['is_pledge', '=', true],
            ['is_recurring', '=', true],
            ['status', '=', 'pledge']
        ])->whereBetween('billing_start_date', [$yesterday->startOfDay(), $yesterday->copy()->endOfDay()])
        ->get();
        
        foreach ($pledges as $pledge){
            $billing_start_date = strtotime(array_get($pledge, 'billing_start_date', Carbon::now()->toDayDateTimeString()));
            $currentPaymentDate = Carbon::createFromTimestamp($billing_start_date);
            
            $billing_end_date = strtotime(array_get($pledge, 'billing_end_date', Carbon::now()->toDayDateTimeString()));
            $endPaymentDate = Carbon::createFromTimestamp($billing_end_date);
            
            $period = array_get($pledge, 'billing_period');
            switch ($period){
                case 'Monthly':
                    $newPromisedPayDate = $currentPaymentDate->copy()->addMonth();
                    break;
                case 'Weekly':
                    $newPromisedPayDate = $currentPaymentDate->copy()->addWeek();
                    break;
                case 'Bi-Weekly':
                    $newPromisedPayDate = $currentPaymentDate->copy()->addWeeks(2);
                    break;
                case 'One Time':
                    $newPromisedPayDate = $currentPaymentDate->copy()->addWeek();
                    break;
                default :
                    break;
            }
            
            $paymentDateTimestamp = strtotime($currentPaymentDate->copy()->toDateString());
            $endPaymentDateTimestamp = strtotime($endPaymentDate->copy()->toDateString());
            
            if( $paymentDateTimestamp >= $endPaymentDateTimestamp ){
                DB::table('transaction_templates')->where('id', array_get($pledge, 'id'))
                ->update(['status' => 'overdue']);
            }
            else{
                DB::table('transaction_templates')->where('id', array_get($pledge, 'id'))
                ->update(['billing_start_date' => $newPromisedPayDate->endOfDay()->toDateString()]);
            }
        }
    }
    
}
