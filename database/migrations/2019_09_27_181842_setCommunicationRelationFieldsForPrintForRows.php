<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetCommunicationRelationFieldsForPrintForRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // contacts
        DB::table('communications AS c')
        ->join('communication_contact AS cc','c.id','=','cc.communication_id')
        ->havingRaw('count(contact_id) = 1')
        ->groupBy('c.id')
        ->where('old_print_for','contact')
        ->whereNull('relation_id')
        ->update(['relation_id' => DB::raw('cc.contact_id'),
            'relation_type' => 'App\\Models\\Contact'
        ]);
        
        
        // Donoros
        DB::table('communications AS c')
        ->where('old_print_for','LIKE','%donor%')
        ->whereNull('relation_id')
        ->update(['relation_id' => 0,
            'relation_type' => 'App\\Models\\Lists'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
