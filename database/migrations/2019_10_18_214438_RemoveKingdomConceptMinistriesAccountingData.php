<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveKingdomConceptMinistriesAccountingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $kcm = DB::table('tenants')->where('subdomain','kingdom-concept-ministries')->first();
        if ($kcm) {
            DB::table('bank_transactions')->where('tenant_id',$kcm->id)->delete();
            DB::table('register_splits')->where('tenant_id',$kcm->id)->delete();
            DB::table('registers')->where('tenant_id',$kcm->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // see https://app.asana.com/0/0/1145648006585274/f for backup data
    }
}
