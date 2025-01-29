<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RegenerateWidgetsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('widgets', function (Blueprint $table) {
            Schema::table('widgets', function (Blueprint $table) {
                $table->dropColumn(['message', 'has_data', 'order', 'size']);
            });

            Schema::table('widgets', function (Blueprint $table) {
                $table->string('size', 45)->default('col-sm-12')->after('parameters');
                $table->integer('order')->nullable()->after('parameters');
                
                $table->unsignedInteger('widget_type_id')->nullable()->after('tenant_id');
                $table->foreign('widget_type_id')->references('id')->on('widget_types')->onUpdate('cascade')->onDelete('cascade');
                
                $table->unsignedInteger('dashboard_id')->nullable()->after('tenant_id');
                $table->foreign('dashboard_id')->references('id')->on('dashboard')->onUpdate('cascade')->onDelete('cascade');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('widgets', function (Blueprint $table) {
            //
        });
    }

}
