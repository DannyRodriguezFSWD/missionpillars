<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class CreateTasksTable extends Migration
{
  use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $this->setTenantIdForeignKey($table);
            
            $table->unsignedInteger('linked_to')->nullable();//contact whom the task was created for
            $table->unsignedInteger('assigned_to')->nullable();//contact whom the task was assigned for
            
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 255)->default('open');
            $table->timestamp('due')->nullable();
            $table->string('category')->nullable();
            $table->tinyInteger('show_time')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('tasks');
    }
}
