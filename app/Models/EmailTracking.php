<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailTracking extends BaseModel {

    protected $table = 'email_tracking';

    public function sentTo() {
        return $this->belongsTo(EmailSent::class, 'email_sent_id', 'id');
    }

    public static function insertIfStatusDoesNotExists($params) {
        $record = DB::table('email_tracking')->where([
            ['tenant_id', '=', array_get($params, 'tenant_id')],
            ['email_sent_id', '=', array_get($params, 'email_sent_id')],
            ['list_id', '=', array_get($params, 'list_id')],
            ['contact_id', '=', array_get($params, 'contact_id')],
            ['status', '=', array_get($params, 'status')]
        ])->first();
        
        if(!$record){
            array_set($params, 'status_timestamp', Carbon::now());
            array_set($params, 'log_level', 'info');
            DB::table('email_tracking')->insert($params);
        }
    }

}
