<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;

use App\Models\StatementTemplate;


class ReplaceWindowedTemplateInStatmentTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        StatementTemplate::noTenantScope()->where('name', 'Window Envelopes')->delete();
        $values = [
            'name' => 'Contribution Stmt. Window Envelope',
            'print_content' => "<div><span style='font-size: 10pt !important'><br><br><br><br>[:organization_name:]<br>Add Your Return Address<br>Add Your Return City, State<br><br><br><br><br><br>[:name:]<br>[:address:]<br><br><br><br></span>    <p>Dear [:first_name:],</p><p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We've listed the contributions you've made below:</p><p>&nbsp;</p><p>[:item_list:]</p><p>&nbsp;</p><p>Thanks again,&nbsp;</p><p>[:organization_name:]</p></div>",
            'email_content' => "<p>[:name:]</p><p>[:address:]</p><p>Dear [:first_name:],</p><p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We've listed the contributions you've made below:</p><p>&nbsp;</p><p>[:item_list:]</p><p>&nbsp;</p><p>Thanks again,&nbsp;</p><p>[:organization_name:]</p>",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        DB::table('statement_templates')->insert($values);
    }
    
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        StatementTemplate::noTenantScope()->where('name', 'Window Envelopes')
        ->whereNull('tenant_id')
        ->withTrashed()
        ->restore();
        StatementTemplate::noTenantScope()->where('name', 'Contribution Stmt. Window Envelope')
        ->whereNull('tenant_id')
        ->forceDelete();
    }
}
