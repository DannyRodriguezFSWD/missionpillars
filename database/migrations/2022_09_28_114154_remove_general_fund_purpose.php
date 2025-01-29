<?php

use App\Models\Purpose;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveGeneralFundPurpose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `purposes` CHANGE COLUMN `type` `type` ENUM('Internal', 'Missionary', 'External', 'Organization', 'Purpose') NULL DEFAULT NULL");
        DB::statement("update transaction_splits ts set purpose_id = (select id from purposes p where p.deleted_at is null and p.tenant_id = ts.tenant_id and p.sub_type = 'organizations') where purpose_id = 1 and (select id from purposes p where p.deleted_at is null and p.tenant_id = ts.tenant_id and p.sub_type = 'organizations') is not null");
        DB::statement("update transaction_template_splits ts set purpose_id = (select id from purposes p where p.deleted_at is null and p.tenant_id = ts.tenant_id and p.sub_type = 'organizations') where purpose_id = 1 and (select id from purposes p where p.deleted_at is null and p.tenant_id = ts.tenant_id and p.sub_type = 'organizations') is not null");
        DB::statement("update purposes set deleted_at = now() where id = 1");
        DB::statement("update purposes set type = 'Organization' where sub_type = 'organizations'");
        DB::statement("update purposes set type = 'Purpose' where type = 'Internal'");
        
        $tenants = Tenant::withoutGlobalScopes()->get();
        foreach ($tenants as $tenant) {
            $tenantId = array_get($tenant, 'id');
            $parentPurpose = Purpose::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('sub_type', 'organizations')->first();
            $parentPurposeId = array_get($parentPurpose, 'id');
            if ($parentPurposeId) {
                DB::statement("update purposes p set parent_purposes_id = $parentPurposeId where tenant_id = $tenantId and type = 'Purpose' and parent_purposes_id is null");
            }
        }   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("update purposes set deleted_at = null where id = 1");
    }
}
