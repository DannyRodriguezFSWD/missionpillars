<?php

namespace App\Classes\Shared\Tricks;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Updates all datetime fields in db (created_at, updated_at, transaction_last_updated_at)
 *
 * @author josemiguel
 */
class TableDatetimeFieldsTrick {

    private $property;

    public function __construct() {
        $this->property = str_replace(':db-name:', env('DB_DATABASE', 'missionpillars_local'), 'Tables_in_:db-name:');
    }

    private function execute($task = null, $params = []) {
        if ($task) {
            $dataset = DB::select('SHOW TABLES');
            foreach ($dataset as $data) {
                $table_name = array_get((array) $data, $this->property);
                if (!is_null($table_name)) {
                    array_set($params, 'table_name', $table_name);
                    call_user_func_array($task, $params);
                }
            }
        }
    }

    /**
     * Initializes DB dates with random values between current DateTime and last year
     */
    public function initialize() {
        $this->execute(function($table_name) {
            $thisYear = date('Y');
            $lastYear = date('Y') - 1;
            
            $thisMonth = date('n');
            
            if (Schema::hasColumn($table_name, 'created_at') && Schema::hasColumn($table_name, 'updated_at')) {
                DB::update("UPDATE $table_name SET created_at = (SELECT STR_TO_DATE(CONCAT(ROUND( RAND() * ($thisYear-$lastYear) + $lastYear ), '-', ROUND( RAND() * ($thisMonth-1) + 1 ), '-', ROUND( RAND() * (28-1) + 1 ), ' 12:00:00'), '%Y-%m-%d %H:%i:%s')), updated_at = (SELECT STR_TO_DATE(CONCAT(ROUND( RAND() * ($thisYear-$lastYear) + $lastYear ), '-', ROUND( RAND() * ($thisMonth-1) + 1 ), '-', ROUND( RAND() * (28-1) + 1 ), ' 12:00:00'), '%Y-%m-%d %H:%i:%s'))");
            }
            
            if ($table_name === 'transactions') {
                //update dates
                DB::update("UPDATE $table_name SET transaction_initiated_at = (SELECT STR_TO_DATE(CONCAT(ROUND( RAND() * ($thisYear-$lastYear) + $lastYear ), '-', ROUND( RAND() * ($thisMonth-1) + 1 ), '-', ROUND( RAND() * (28-1) + 1 ), ' 12:00:00'), '%Y-%m-%d %H:%i:%s')), transaction_last_updated_at = (SELECT STR_TO_DATE(CONCAT(ROUND( RAND() * ($thisYear-$lastYear) + $lastYear ), '-', ROUND( RAND() * ($thisMonth-1) + 1 ), '-', ROUND( RAND() * (28-1) + 1 ), ' 12:00:00'), '%Y-%m-%d %H:%i:%s'))");
                //update device category
                DB::update("UPDATE $table_name SET device_category = ELT(1 + FLOOR(RAND()*3), 'tablet', 'phone', 'desktop') WHERE device_category IS NULL");
                //update transaction path
                DB::update("UPDATE $table_name SET transaction_path = ELT(1 + FLOOR(RAND()*7), 'text', 'facebook', 'virtual terminal', 'continue to give', 'kiosk', 'badge', 'givers app') WHERE transaction_path IS NULL");
            }
            
            if ($table_name === 'calendar_event_template_splits') {
                DB::update("UPDATE $table_name SET end_date = start_date WHERE end_date < start_date");
            }
        });
    }

    /**
     * Adds +N days to db stored dates
     */
    public function addDays($number = 1) {
        $this->execute(function($number, $table_name) {
            if (Schema::hasColumn($table_name, 'created_at') && Schema::hasColumn($table_name, 'updated_at')) {
                DB::update("UPDATE $table_name SET created_at = DATE_ADD(created_at, INTERVAL $number DAY), updated_at = NOW()");
            }

            if ($table_name === 'transactions') {
                //update dates
                DB::update("UPDATE $table_name SET transaction_initiated_at = IF(transaction_initiated_at IS NULL, NOW(), DATE_ADD(transaction_initiated_at, INTERVAL $number DAY)), transaction_last_updated_at = IF(transaction_last_updated_at IS NULL, NOW(), DATE_ADD(transaction_last_updated_at, INTERVAL $number DAY)) WHERE TIMESTAMPDIFF(DAY, transaction_initiated_at, NOW()) > $number");
                //update device category
                DB::update("UPDATE $table_name SET device_category = ELT(1 + FLOOR(RAND()*3), 'tablet', 'phone', 'desktop') WHERE device_category IS NULL");
                //update transaction path
                DB::update("UPDATE $table_name SET transaction_path = ELT(1 + FLOOR(RAND()*7), 'text', 'facebook', 'virtual terminal', 'continue to give', 'kiosk', 'badge', 'givers app') WHERE transaction_path IS NULL");
            }
        }, ['number' => $number]);
    }

}
