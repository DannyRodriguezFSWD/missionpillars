<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableContactsAddCcbFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('confirmed_no_allergies')->after('profile_image')->nullable();
            $table->string('allergies', 1000)->after('confirmed_no_allergies')->nullable();
            $table->date('anniversary')->after('marital_status')->nullable();
            $table->string('legal_first_name')->after('preferred_name')->nullable();
            $table->date('deceased')->after('allergies')->nullable();
            $table->string('membership_type')->after('deceased')->nullable();
            $table->date('membership_start_date')->after('membership_type')->nullable();
            $table->date('membership_end_date')->after('membership_start_date')->nullable();
            $table->boolean('active')->after('membership_end_date')->nullable();
            $table->boolean('baptized')->after('active')->nullable();
            $table->date('background_check')->after('baptized')->nullable();
            $table->boolean('limited_access_user')->after('background_check')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_no_allergies', 'allergies', 'anniversary', 'legal_first_name', 'deceased', 
                'membership_type', 'membership_start_date', 'membership_end_date', 'active', 'baptized',
                'background_check', 'limited_access_user'
            ]);
        });
    }
}
