<?php

use App\Models\StatementTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class UpdateStatementTemplatesContentAddEinAndSmallChanges extends Migration
{
    private $tablename = 'statement_templates';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $content1 = "<p>[:title:] [:preferred-fallback-to-first-last-name:]</p>
            <p>[:address:]</p>
            <p>Dear [:title:] [:preferred-fallback-to-first-last-name:],</p>
            <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] (EIN = [:ein:]) received between [:start_date:] and [:end_date:]. In accordance with the IRS guidelines, this letter is a written acknowledgment of the donation for tax records. No goods or services of any value were or will be transferred to you in connection with this donation. We've listed the contributions you've made below:</p>
            <p>&nbsp;</p>
            <p>[:item_list:]</p>
            <p>&nbsp;</p>
            <p>Printed On [:date_today:]</p>
            <p>Thanks again,&nbsp;</p>
            <p>[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Standard')->update(['content' => $content1]);

        $content2 = "<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">
                    <tbody>
                    <tr>
                    <td style=\"width: 28.7942%;\">&nbsp;</td>
                    <td style=\"width: 71.2058%;\">
                    <p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                    </td>
                    </tr>
                    </tbody>
                    </table>
                    <p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                    <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] (EIN = [:ein:]) received between [:start_date:] and [:end_date:]. In accordance with the IRS guidelines, this letter is a written acknowledgment of the donation for tax records. No goods or services of any value were or will be transferred to you in connection with this donation. We've listed the contributions you've made below:</p>
                    <p>&nbsp;</p>
                    <p>[:item_list:]</p>
                    <p>&nbsp;</p>
                    <p>Printed On [:date_today:]</p>
                    <p>Sincerely, <br />[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Window Envelopes')->update(['content' => $content2]);


        $content3 = "<p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] (EIN = [:ein:]) received between [:start_date:] and [:end_date:]. In accordance with the IRS guidelines, this letter is a written acknowledgment of the donation for tax records. No goods or services of any value were or will be transferred to you in connection with this donation. We've listed the contributions you've made below:</p>
                <p>Printed On [:date_today:]</p>
                <p>Sincerely, <br />[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Minimalist')->update(['content' => $content3]);
        $content4 = "<div><span style='font-size: 10pt !important'><br><br><br><br>[:organization_name:]<br>Add Your Return Address<br>Add Your Return City, State<br><br><br><br><br><br>[:title:] [:preferred-fallback-to-first-last-name:]<br>[:address:]<br><br><br><br></span>    <p>Dear [:title:] [:preferred-fallback-to-first-last-name:],</p><p>Thank you for your contributions of [:total_amount:] that [:organization_name:] (EIN = [:ein:]) received between [:start_date:] and [:end_date:]. In accordance with the IRS guidelines, this letter is a written acknowledgment of the donation for tax records. No goods or services of any value were or will be transferred to you in connection with this donation. We've listed the contributions you've made below:</p><p>&nbsp;</p><p>[:item_list:]</p><p>&nbsp;</p><p>Printed On [:date_today:]</p><p>Thanks again,&nbsp;</p><p>[:organization_name:]</p></div>";
        DB::table($this->tablename)->where('name', 'Contribution Stmt. Window Envelope')->update(['content' => $content4]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $content1 = "<p>[:title:] [:preferred-fallback-to-first-last-name:]</p>
                <p>[:address:]</p>
                <p>Dear [:title:] [:preferred-fallback-to-first-last-name:],</p>
                <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We've listed the contributions you've made below:</p>
                <p>&nbsp;</p>
                <p>[:item_list:]</p>
                <p>&nbsp;</p>
                <p>Thanks again,&nbsp;</p>
                <p>[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Standard')->update(['content' => $content1]);

        $content2 = "<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">
                <tbody>
                <tr>
                <td style=\"width: 28.7942%;\">&nbsp;</td>
                <td style=\"width: 71.2058%;\">
                <p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                </td>
                </tr>
                </tbody>
                </table>
                <p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We've listed the contributions you've made below:</p>
                <p>&nbsp;</p>
                <p>[:item_list:]</p>
                <p>&nbsp;</p>
                <p>Sincerely, <br />[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Window Envelopes')->update(['content' => $content2]);


        $content3 = "<p>[:title:] [:preferred-fallback-to-first-last-name:]<br />[:address:]</p>
                <p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.</p>
                <p>Sincerely, <br />[:organization_name:]</p>";
        DB::table($this->tablename)->where('name', 'Minimalist')->update(['content' => $content3]);
        $content4 = "<div><span style='font-size: 10pt !important'><br><br><br><br>[:organization_name:]<br>Add Your Return Address<br>Add Your Return City, State<br><br><br><br><br><br>[:title:] [:preferred-fallback-to-first-last-name:]<br>[:address:]<br><br><br><br></span>    <p>Dear [:title:] [:preferred-fallback-to-first-last-name:],</p><p>Thank you for your contributions of [:total_amount:] that [:organization_name:] received between [:start_date:] and [:end_date:]. No goods or services were provided in exchange for your contributions.&nbsp; We've listed the contributions you've made below:</p><p>&nbsp;</p><p>[:item_list:]</p><p>&nbsp;</p><p>Thanks again,&nbsp;</p><p>[:organization_name:]</p></div>";
        DB::table($this->tablename)->where('name', 'Contribution Stmt. Window Envelope')->update(['content' => $content4]);

    }
}
