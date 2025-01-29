<?php

use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    //return $request->user();
    return auth()->user();
});
*/
/*
Route::resource('accountgroups', 'AccountGroupsController', [
    'except' => ['create', 'edit', 'show']
]);
*/
/*
Route::resource('accounts', 'AccountsController', [
    'except' => ['index', 'create', 'edit', 'show']
]);
*/
//Route::get('funds', 'AccountGroupsController@showFunds');
//Route::post('funds', 'AccountGroupsController@createFunds');
//Route::get('accounts', 'AccountsController@indexApi');
//Route::get('journal-entries', 'JournalEntriesController@fetchData');
//Route::get('registers/table', 'RegistersController@getTableData');
//Route::get('registers/getSplits', 'RegistersController@getSplits');
//Route::get('bank-accounts/getBankData', 'BankAccountsController@getBankData');
//Route::get('journal-entries/getSplits', 'JournalEntriesController@getSplits');
//Route::post('registers/update', 'RegistersController@update');
//Route::delete('registers/{id}', 'RegistersController@destroy');
//Route::get('reports/balance-sheet', 'ReportsController@getFilteredDataBalanceSheet');
//Route::get('reports/income-statement', 'ReportsController@getISData');
// Route::get('reports/bs-report-download', 'ReportsController@pdfDownload')->name('accounting.bs-report-download');
//Route::post('accounts/sortorder', 'AccountsController@bulkUpdate');
//Route::post('accountgroups/sortorder', 'AccountGroupsController@bulkUpdate');
//Route::post('link-accounts', 'BankAccountsController@linkAccount');
//Route::get('transactions', 'BankAccountsController@getAccountTransactions');
//Route::post('transactions', 'BankAccountsController@mapAccountTransactions');
//Route::post('transactions-bulk', 'BankAccountsController@mapAccountTransactionsBulk');
//Route::post('sync-transactions', 'BankAccountsController@syncTransactions');
//Route::post('sync-single-transaction', 'BankAccountsController@syncSingleTransaction');


Route::group(['prefix' => 'v1', 'namespace' => 'API', 'middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/transactions', 'TransactionsController@getTransactions');
    Route::post('/transactions', 'TransactionsController@postTransactions');

    Route::get('/sync', 'SyncController@getSynchronized');
    Route::post('/sync', 'SyncController@setSynchronized');


});

Route::group(['prefix' => 'mailgun'], function (){
    Route::post('webhook','MailgunWebhooksController@index');
});

// Route::middleware(['auth', 'checkuid'])->prefix('accounting')->group(function () {
//     // Route::get('/transactions', 'AccountingController@index')->name('accounting.index');
//     Route::resource('accounts', 'AccountsController', ['except' => ['edit', 'show', 'store']]);
//     Route::post('accounts/createAccount', 'AccountsController@storeAccount')->name('store-account');
//     Route::post('accounts/createGroup', 'AccountsController@storeGroup')->name('store-group');
// });

//Route::get('/sync', 'API\SyncController@getSynchronized');
//Route::post('/sync', 'API\SyncController@setSynchronized');
