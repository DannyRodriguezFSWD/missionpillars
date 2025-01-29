<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateContactsTable extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable(); //records the contact info for users in crm. if null then contact is not a system user
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->string('first_name', 45)->nullable();
            $table->string('middle_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('preferred_name', 45)->nullable();
            $table->date('dob')->nullable();
            $table->string('email_1', 45)->nullable();
            $table->string('email_2', 45)->nullable();
            $table->string('home_phone', 45)->nullable();
            $table->string('cell_phone', 45)->nullable();
            $table->string('work_phone', 45)->nullable();
            $table->string('gender', 45)->nullable();
            $table->smallInteger('head_of_household')->default(0);
            $table->string('prefix', 45)->nullable();
            $table->string('salutation', 45)->nullable();
            $table->string('source', 45)->nullable();
            $table->string('website', 45)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('facebook_id', 45)->nullable();
            $table->string('twitter', 45)->nullable();
            $table->date('death_date')->nullable();
            $table->smallInteger('do_not_contact')->default(1);
            $table->string('marital_status', 45)->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            $this->trackingFields($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
