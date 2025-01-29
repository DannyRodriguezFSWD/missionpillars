<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCmapaignsChartOfAccountIdToPurposeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
ALTER TABLE `campaigns`
	DROP FOREIGN KEY IF EXISTS `campaigns_chart_of_account_id_foreign`;
    ');
    DB::statement('
ALTER TABLE `campaigns`
	CHANGE COLUMN IF EXISTS `chart_of_account_id` `purpose_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `contact_id`,
	DROP INDEX IF EXISTS `campaigns_chart_of_account_id_foreign`,
	ADD INDEX `campaigns_purpose_id_foreign` (`purpose_id`) USING BTREE,
	ADD CONSTRAINT `campaigns_purpose_id_foreign` FOREIGN KEY (`purpose_id`) REFERENCES `purposes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
        ');
        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->renameColumn('chart_of_account_id', 'purpose_id');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('
        ALTER TABLE `campaigns`
            DROP FOREIGN KEY IF EXISTS `campaigns_purpose_id_foreign`;
            ');
            DB::statement('
        ALTER TABLE `campaigns`
            CHANGE COLUMN IF EXISTS `purpose_id` `chart_of_account_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `contact_id`,
            DROP INDEX IF EXISTS `campaigns_purpose_id_foreign`,
            ADD INDEX `campaigns_chart_of_account_id_foreign` (`chart_of_account_id`) USING BTREE,
            ADD CONSTRAINT `campaigns_chart_of_account_id_foreign` FOREIGN KEY (`chart_of_account_id`) REFERENCES `purposes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
                ');
        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->renameColumn('purpose_id', 'chart_of_account_id');
        // });
    }
}
