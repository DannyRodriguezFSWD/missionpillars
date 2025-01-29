<?php

use App\Models\Group;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class AddGroupUuid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $groups = Group::withoutGlobalScopes()->whereNull('uuid')->get();
        
        foreach ($groups as $group) {
            $id = array_get($group, 'id');
            $uuid = Uuid::uuid1();
            DB::statement("update groups set uuid = '$uuid' where id = $id");
        }
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
