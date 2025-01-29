<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSuperAdminToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_super_admin')->default(false)->after('email');
        });
        DB::statement('UPDATE users SET is_super_admin = true WHERE email IN ("immanuel@continuetogive.com","immanuelcomer@gmail.com","jessewellhoefer@continuetogive.com")');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('is_super_admin');
        });
    }
}
