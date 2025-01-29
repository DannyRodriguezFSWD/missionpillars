<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatementTemplatesTableForCommunicationUpgrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statement_templates', function (Blueprint $table) {
            $table->renameColumn('print_content', 'content');
            $table->dropColumn('email_content');
        });
        
        Schema::table('statement_templates', function (Blueprint $table) {
            $table->longText('content_json')->nullable()->after('content');
            $table->string('editor_type')->default('tiny')->after('content_json');
        });
        
        $this->insertTopolGlobalTempaltes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statement_templates', function (Blueprint $table) {
            $table->renameColumn('content', 'print_content');
        });
        
        Schema::table('statement_templates', function (Blueprint $table) {
            $table->longText('email_content')->nullable()->after('print_content');
            $table->dropColumn(['content_json', 'editor_type']);
        });
        
        $this->deleteTopolGlobalTemplates();
        
        DB::statement('UPDATE statement_templates SET email_content = print_content');
    }
    
    private function insertTopolGlobalTempaltes()
    {
        $rawQuery = "INSERT INTO `statement_templates` (`id`, `name`, `content`, `content_json`, `editor_type`, `created_at`, `updated_at`) VALUES (null, 'Blank', '<!doctype html>\n    <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">\n      <head>\n        <title>\n          \n        </title>\n        <!--[if !mso]><!-- -->\n        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n        <!--<![endif]-->\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n        <style type=\"text/css\">\n          #outlook a { padding:0; }\n          .ReadMsgBody { width:100%; }\n          .ExternalClass { width:100%; }\n          .ExternalClass * { line-height:100%; }\n          body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }\n          table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }\n          img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }\n          p { display:block;margin:13px 0; }\n        </style>\n        <!--[if !mso]><!-->\n        <style type=\"text/css\">\n          @media only screen and (max-width:480px) {\n            @-ms-viewport { width:320px; }\n            @viewport { width:320px; }\n          }\n        </style>\n        <!--<![endif]-->\n        <!--[if mso]>\n        <xml>\n        <o:OfficeDocumentSettings>\n          <o:AllowPNG/>\n          <o:PixelsPerInch>96</o:PixelsPerInch>\n        </o:OfficeDocumentSettings>\n        </xml>\n        <![endif]-->\n        <!--[if lte mso 11]>\n        <style type=\"text/css\">\n          .outlook-group-fix { width:100% !important; }\n        </style>\n        <![endif]-->\n        \n      <!--[if !mso]><!-->\n        <link href=\"https://fonts.googleapis.com/css?family=Cabin:400,700\" rel=\"stylesheet\" type=\"text/css\">\n        <style type=\"text/css\">\n          @import url(https://fonts.googleapis.com/css?family=Cabin:400,700);\n        </style>\n      <!--<![endif]-->\n\n    \n        \n    <style type=\"text/css\">\n      @media only screen and (min-width:480px) {\n        .mj-column-per-100 { width:100% !important; max-width: 100%; }\n      }\n    </style>\n    \n  \n        <style type=\"text/css\">\n        \n        \n        </style>\n        <style type=\"text/css\">.hide_on_mobile { display: none !important;} \n        @media only screen and (min-width: 480px) { .hide_on_mobile { display: block !important;} }\n        .hide_section_on_mobile { display: none !important;} \n        @media only screen and (min-width: 480px) { .hide_section_on_mobile { display: table !important;} }\n        .hide_on_desktop { display: block !important;} \n        @media only screen and (min-width: 480px) { .hide_on_desktop { display: none !important;} }\n        .hide_section_on_desktop { display: table !important;} \n        @media only screen and (min-width: 480px) { .hide_section_on_desktop { display: none !important;} }\n        [owa] .mj-column-per-100 {\n            width: 100%!important;\n          }\n          [owa] .mj-column-per-50 {\n            width: 50%!important;\n          }\n          [owa] .mj-column-per-33 {\n            width: 33.333333333333336%!important;\n          }\n          p, h1, h2, h3 {\n              margin: 0px;\n          }\n\n          a {\n              text-decoration: none;\n              color: inherit;\n          }\n        \n          @media only print and (min-width:480px) {\n            .mj-column-per-100 { width:100%!important; }\n            .mj-column-per-40 { width:40%!important; }\n            .mj-column-per-60 { width:60%!important; }\n            .mj-column-per-50 { width: 50%!important; }\n            mj-column-per-33 { width: 33.333333333333336%!important; }\n            }</style>\n        \n      </head>\n      <body style=\"background-color:#FFFFFF;\">\n        \n        \n      <div style=\"background-color:#FFFFFF;\">\n        \n      \n      <!--[if mso | IE]>\n      <table\n         align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"\" style=\"width:600px;\" width=\"600\"\n      >\n        <tr>\n          <td style=\"line-height:0px;font-size:0px;mso-line-height-rule:exactly;\">\n      <![endif]-->\n    \n      \n      <div style=\"Margin:0px auto;max-width:600px;\">\n        \n        <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"width:100%;\">\n          <tbody>\n            <tr>\n              <td style=\"direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;vertical-align:top;\">\n                <!--[if mso | IE]>\n                  <table role=\"presentation\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n                \n        <tr>\n      \n            <td\n               class=\"\" style=\"vertical-align:top;width:600px;\"\n            >\n          <![endif]-->\n            \n      <div class=\"mj-column-per-100 outlook-group-fix\" style=\"font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;\">\n        \n      <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\" style=\"vertical-align:top;\" width=\"100%\">\n        \n      </table>\n    \n      </div>\n    \n          <!--[if mso | IE]>\n            </td>\n          \n        </tr>\n      \n                  </table>\n                <![endif]-->\n              </td>\n            </tr>\n          </tbody>\n        </table>\n        \n      </div>\n    \n      \n      <!--[if mso | IE]>\n          </td>\n        </tr>\n      </table>\n      <![endif]-->\n    \n    \n      </div>\n    \n      </body>\n    </html>', '{\"tagName\":\"mj-global-style\",\"attributes\":{\"h1:color\":\"#000\",\"h1:font-family\":\"Helvetica, sans-serif\",\"h2:color\":\"#000\",\"h2:font-family\":\"Ubuntu, Helvetica, Arial, sans-serif\",\"h3:color\":\"#000\",\"h3:font-family\":\"Ubuntu, Helvetica, Arial, sans-serif\",\":color\":\"#000\",\":font-family\":\"Ubuntu, Helvetica, Arial, sans-serif\",\":line-height\":\"1.5\",\"a:color\":\"#24bfbc\",\"button:background-color\":\"#e85034\",\"containerWidth\":600,\"fonts\":\"Helvetica,sans-serif,Ubuntu,Arial\",\"mj-text\":{\"line-height\":1.5,\"font-size\":15},\"mj-button\":[]},\"children\":[{\"tagName\":\"mj-body\",\"attributes\":{\"background-color\":\"#FFFFFF\",\"containerWidth\":600},\"children\":[{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":false,\"padding\":\"9px 0px 9px 0px\"},\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[],\"uid\":\"HJQ8ytZzW\"}],\"layout\":1,\"backgroundColor\":null,\"backgroundImage\":null,\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"Byggju-zb\"}]}],\"style\":{\"h1\":{\"font-family\":\"\\\\\\\"Cabin\\\\\\\", sans-serif\"},\"a\":{\"color\":\"#0000EE\"}},\"fonts\":[\"\\\\\\\"Cabin\\\\\\\", sans-serif\"]}', 'topol', now(), now());";
        
        DB::statement($rawQuery);
    }
    
    private function deleteTopolGlobalTemplates()
    {
        $rawQuery = "DELETE FROM statement_templates WHERE name = 'Blank';";
        
        DB::statement($rawQuery);
    }
}
