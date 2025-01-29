<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeUsersAndRolesFeaturesToModuleFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $chms_features = DB::table('features')
        ->select(DB::raw('1 AS module_id'),'id AS feature_id')
        ->whereIn('name',
            [ 
                'crm-users',
                'crm-roles',
            ]
        )->get()->map(function($x){ return (array) $x; })->toArray();
        DB::table('module_features')->insert($chms_features);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $users_and_roles_features = DB::table('features')->whereIn('name', [ 
            'crm-users',
            'crm-roles',
        ]
        )->pluck('id')->toArray();
        DB::table('module_features')
        ->where('module_id',1)
        ->whereIn('feature_id', $users_and_roles_features)
        ->delete();
    }
}
