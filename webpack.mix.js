const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
/*
mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');
*/
mix.webpackConfig({
    node: {
        fs: 'empty'
    },
    resolve: {
        alias: {
            "handlebars" : "handlebars/dist/handlebars.js"
        }
    }
})
mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css');
mix.sass('resources/assets/sass/custom_datatable.scss', 'public/css')
mix.js('resources/assets/js/contact-search.js', 'public/js')

mix.js('resources/assets/js/crm-communications-components.js', 'public/js')
mix.js('resources/assets/js/crm-reports-components.js', 'public/js')
mix.js('resources/assets/js/crm-software-billing-upgrade.js', 'public/js');
mix.js('resources/assets/js/crm-software-billing-upgrade-modal.js', 'public/js');
mix.js('resources/assets/js/crm-transactions.js', 'public/js');
mix.js('resources/assets/js/crm-login.js', 'public/js');
mix.js('resources/assets/js/crm-communications-configure.js', 'public/js');

mix.js('resources/assets/js/accounts-components.js', 'public/js');
mix.js('resources/assets/js/starting-balances.js', 'public/js');
mix.js('resources/assets/js/accounting-registers.js', 'public/js');
mix.js('resources/assets/js/accounting-reports.js', 'public/js');
mix.js('resources/assets/js/accounting-journal-entries.js', 'public/js');
mix.js('resources/assets/js/accounting-bank-integration-acc-list.js', 'public/js');
mix.js('resources/assets/js/accounting-fund-transfer-entries.js', 'public/js');
mix.js('resources/assets/js/crm-forms-index.js', 'public/js');
mix.js('resources/assets/js/people-search-with-create.js', 'public/js');
mix.js('resources/assets/js/people-search-with-create2.js', 'public/js');
mix.js('resources/assets/js/family-search-with-create.js', 'public/js');
mix.js('resources/assets/js/group-search-with-create.js', 'public/js');
mix.js('resources/assets/js/tasks.js', 'public/js');