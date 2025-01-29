<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Description of CustomObserver
 *
 * @author josemiguel
 */
class CustomTrackingObserver {

    public $event;
    public $model;
    
    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';
    const CREATED_BY_SESSION_ID = 'created_by_session_id';
    const UPDATED_BY_SESSION_ID = 'updated_by_session_id';

    public function __construct($event, array $data) {
        $this->event = $event;
        $this->model = $data[0];
    }

    /**
     * Updates created_by, created_by_session_id tracking fields if available 
     * in current model table
     * @return void
     */
    public function created() {
        $table = $this->model->getTable();
        if (Schema::hasColumn($table, self::CREATED_BY)) {
            DB::table($table)
                    ->where('id', $this->model->id)
                    ->update([self::CREATED_BY => Auth::id()]);
        }

        if (Schema::hasColumn($table, self::CREATED_BY_SESSION_ID)) {
            DB::table($table)
                    ->where('id', $this->model->id)
                    ->update([self::CREATED_BY_SESSION_ID => Session::getId()]);
        }
    }
    
    /**
     * Updates updated_by, updated_by_session_id tracking fields if available
     * in current model table
     * @return void
     */
    public function updated() {
        $table = $this->model->getTable();
        if (Schema::hasColumn($table, self::UPDATED_BY)) {
            DB::table($table)
                    ->where('id', $this->model->id)
                    ->update([self::UPDATED_BY => Auth::id()]);
        }

        if (Schema::hasColumn($table, self::UPDATED_BY_SESSION_ID)) {
            DB::table($table)
                    ->where('id', $this->model->id)
                    ->update([self::UPDATED_BY_SESSION_ID => Session::getId()]);
        }
    }

}
