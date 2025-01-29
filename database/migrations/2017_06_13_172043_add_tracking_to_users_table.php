<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;


class AddTrackingToUsersTable extends Migration {
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $this->trackingFields($table);
            $table->integer('c2g_id')->nullable();
            $table->tinyInteger('has_migrated_c2g')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
            $table->dropColumn('c2g_id');
            $this->dropTrackingFields($table);
        });
    }

}
