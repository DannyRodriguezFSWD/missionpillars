<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

/**
 * Custom methods for migrations without hardcode Laravel Core
 * @author josemiguel
 */
trait CustomBlueprint {

    /**
     * Set up tracking fields to current table migration     
     * @param Blueprint $table
     * @return Void
     */
    public function trackingFields(Blueprint $table) {
        $table->unsignedInteger('created_by')->nullable();
        $table->unsignedInteger('updated_by')->nullable();
        $table->string('created_by_session_id', 255)->nullable();
        $table->string('updated_by_session_id', 255)->nullable();
    }
    
    /**
     * Drops tracking fields in current table 
     * @param Blueprint $table
     * @return Void
     */
    public function dropTrackingFields(Blueprint $table) {
        $table->dropColumn('created_by');
        $table->dropColumn('updated_by');
        $table->dropColumn('created_by_session_id');
        $table->dropColumn('updated_by_session_id');
    }

    /**
     * Set up user_id foreign key to current table migration (use only after trackingFields funtion)
     * @param Blueprint $table
     * @return Void
     */
    public function setUserIdForeignKey(Blueprint $table) {
        $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
    }

    /**
     * Set up tenant_id foreign key to current table migration (use only after create tenant_id field)
     * @param Blueprint $table
     * @return Void
     */
    public function setTenantIdForeignKey(Blueprint $table) {
        $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
    }

}
