<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function(){
    return redirect()->route('dashboard.index');
})->name('dashboard.index');

Route::get('/maintenance/mode', function () {
    return view('errors.cloudflare');
});

Route::resource('tenants', 'TenantsController');
Route::get('upgrade', 'TenantsController@upgrade')->name('tenant.upgrade.modules');
Route::get('upgrade/features', 'TenantsController@publicUpgrade')->name('tenant.upgrade.modules.public');
Route::get('go/{page}/{uuid}', 'TenantsController@go')->name('tenants.go');

Auth::routes();

Route::post('/login', 'Auth\LoginController@loginInTenant');

Route::get('unsubscribe/{uuid}', 'EmailsController@unsubscribe')->name('emails.unsubscribe');
Route::post('subscribe/contact', 'EmailsController@subscribeContact')->name('emails.subscribe.contact');
Route::post('unsubscribe/contact', 'EmailsController@unsubscribeContact')->name('emails.unsubscribe.contact');
Route::get('unsubscribed', 'EmailsController@unsubscribed')->name('emails.unsubscribed');
Route::get('contacts/{id}/perm-unsubscribe', 'ContactsController@unsubscribePermanently')->name('contacts.perm-unsubscribe');

Route::get('pledges/{id}/cancel', 'PledgesController@cancel')->name('pledges.cancel');
Route::delete('pledges/{id}/cancel', 'PledgesController@cancelPledge')->name('pledges.cancel.pledge');
Route::get('pledges/{id}/canceled', 'PledgesController@canceled')->name('pledges.canceled');

Route::get('communications/{uuid}/public', 'CommunicationsController@publicView')->name('communications.public');
Route::get('emails/{uuid}/web', 'EmailsController@webView')->name('email.web');

Route::resource('join', 'JoinController');
Route::post('join/{id}/join', 'JoinController@join')->name('join.join');

/* Routes without Auth required to login, register*/
Route::resource('oneclick', 'OneClickController');
Route::get('/sso', 'OneClickController@create');

Route::post('/signin', 'Auth\LoginController@signin')->name('signin');
Route::get('customlogin', 'Auth\LoginController@customlogin')->name('customlogin');
Route::post('customregister', 'Auth\RegisterController@customregister')->name('customregister');

// Used by plaid for outh redirects
Route::get('oauth-page', function () {
    return view('oauth-page');
})->name('oauth-page');

Route::middleware([ App\Http\Middleware\FrameHeadersMiddleware::class ])->group(function () {
    Route::get('forms/{id}/public', 'FormsController@share')->name('forms.share');
    Route::get('forms/{id}/iframe', 'FormsController@iframe')->name('forms.iframe');
    Route::post('forms/{id}/submit', 'FormsController@submit')->name('forms.submit');
    Route::get('forms/{id}/payment', 'FormsController@payment')->name('forms.public.payment');
    // TODO FormsController@transaction does not exist
    Route::post('forms/{id}/payment/submit', 'FormsController@transaction')->name('forms.submit.payment');

    // Finish
    Route::get('forms/{id}/finish/{xcode}', 'FormsController@finish')->name('forms.finish');
    Route::get('forms/{id}/finish/redirect/thanks', 'FormsController@finishScreen')->name('forms.finish.screen');

    Route::get('forms/{id}/tags', 'FormsController@tags')->name('forms.tags');
    Route::post('forms/{id}/formtags', 'FormsController@formTags')->name('forms.formtags');

    //public calendars
    Route::get('calendar/{id}/public', 'CalendarsController@share')->name('calendar.share');
    Route::get('calendar/{id}/public/list', 'CalendarsController@shareCalendarListMode')->name('calendar.shareCalendarListMode');

    // public events
    Route::get('events/all/public', 'CalendarEventsController@all')->name('events.all');
    Route::get('events/{id}/share', 'CalendarEventsController@share')->name('events.share');
    Route::get('events/{id}/search', 'CalendarEventsController@publicDirectorySearch')->name('events.public.search');

    Route::get('events/{id}/tickets', 'CalendarEventsController@purchaseTickets')->name('events.purchase.tickets');
    Route::post('events/{id}/tickets', 'CalendarEventsController@purchaseTicketsCheckout')->name('events.purchase.tickets');

    Route::post('events/{id}/signin', 'CalendarEventsController@signin')->name('events.signin');
    Route::get('events/{id}/payment', 'CalendarEventsController@payment')->name('events.public.payment');
    // TODO CalendarEventsController@transaction does not exist
    Route::post('events/{id}/payment', 'CalendarEventsController@transaction')->name('events.public.payment');

    // finish
    Route::get('events/{id}/finish/{c}/{r}/tickets', 'CalendarEventsController@finishTickets')->name('events.finish.tickets');
    Route::get('events/{id}/finish/{xcode}', 'CalendarEventsController@finish')->name('events.finish');
    Route::post('events/tickets/credentials', 'CalendarEventsController@ticket_credentials')->name('events.ticket_credentials');
    // used as final screen on events/checkin
    Route::get('events/{id}/redirect/thanks', 'CalendarEventsController@finishScreen')->name('events.finish.screen');

    // AJAX
    Route::get('events/ajax/get', 'CalendarEventsController@ajaxGet')->name('events.ajax.get');

    Route::get('countries/ajax/autocomplete', 'AjaxController@countriesAutocomplete')->name('countries.autocomplete');
    Route::post('ajax/public/contacts/autocomplete', 'AjaxController@publicContactsAutocomplete')->name('public.contacts.autocomplete');

    // Child Check-in
    Route::get('child-checkin/about', 'ChildrenController@about')->middleware('auth')->name('child-checkin.about');
    Route::resource('child-checkin', 'ChildrenController');
    Route::get('child-checkin/parent/search', 'ChildrenController@searchParent')->name('child-checkin.parent.search');
    Route::get('child-checkin/relative/{id}/create', 'ChildrenController@createRelative')->name('child-checkin.relative.create');

    // Pledges
    Route::get('pledges/{uuid}/share/public', 'PledgeFormsController@share')->name('pledges.share');
    Route::post('pledges/{uuid}/share/submit', 'PledgeFormsController@submit')->name('pledges.submit');

    Route::get('/newsletter', 'SMSController@showTwilioRegisterForm')->name('newsletter');
    Route::post('/newsletter', 'SMSController@storeTwilioRegisterForm')->name('newsletter.store');
    Route::get('/newsletter-success', 'SMSController@showTwilioRegisterFormSuccess')->name('newsletter.success');
    Route::get('/privacy', 'SMSController@showTwilioPrivacy')->name('newsletter.privacy');
});

Route::resource('qr', 'QRCodesController');
Route::resource('redirect', 'RedirectsController');

/******ERROR VIEWS*********/
Route::get('/cheating', function() { return view('errors.cheating'); })->name('cheating');
Route::get('/subdomain', function() { return view('errors.subdomain'); })->name('subdomain');
Route::get('/system/logout', function(){
        $url = sprintf(env('APP_DOMAIN'), auth()->user()->tenant->subdomain);
        auth()->logout();
        return redirect($url);
})->name('system.logout');
Route::get('promo-codes', 'PromocodesController@getPromoCode');
Route::get('promo-codes/check', 'PromocodesController@validatePromoCode');

/*------ Auth protected routes --------*/
Route::middleware(['auth'])->group(function () {
    Route::get('/new_menu_items', 'NewMenuItemsController@index')->name('add.new.menu.items.index');
    Route::post('/new_menu_items', 'NewMenuItemsController@store')->name('add.new.menu.items.store');
    Route::patch('/new_menu_items/{item}', 'NewMenuItemsController@update');
    Route::delete('/new_menu_items/{item}', 'NewMenuItemsController@destroy');
});

Route::middleware(['auth', 'checkuid'])->prefix('crm')->group(function () {
    Route::get('dismiss/alert', 'DashboardController@dismissAlert')->name('dismiss.alert');
    //Route::get('/', 'HomeController@index');
    Route::get('/', 'DashboardController@index');

    //Route::get('/dashboard', 'HomeController@index')->name('dashboard');

    Route::get('dashboard/help', 'DashboardController@help')->name('dashboard.help');
    Route::resource('dashboard', 'DashboardController');
    Route::get('dashboard/user/click/noc2g', 'DashboardController@noc2g')->name('dashboard.click.noc2g');

    Route::resource('widgets', 'WidgetsController');
    Route::post('dashboard/reorder/widgets', 'DashboardController@reorder')->name('dashboard.reorder');

    Route::resource('users', 'UsersController');
    Route::post('users/autocomplete', 'AjaxController@searchInUsers')->name('users.autocomplete');

    Route::resource('roles', 'RolesController');
    Route::resource('tenants', 'TenantsController');
    Route::post('tenants/updateInfo','TenantsController@updateInfo')->name('tenants.updateInfo');

    Route::get('contacts/directory', 'ContactsController@directory')->name('contacts.directory');
    Route::get('contacts/edit-profile', 'ContactsController@editProfile')->name('contacts.edit-profile');
    Route::post('contacts/directory/search', 'ContactsController@directorySearch')->name('contacts.directory.search');
    Route::resource('contacts', 'ContactsController');
    Route::get('contacts/import/{id?}', 'ContactsController@import')->name('contacts.import');
    Route::post('contacts/upload/data/sheet', 'ContactsController@uploadDataSheet')->name('contacts.upload.data.sheet');
    Route::post('contacts/{id?}/relatives/delete', 'ContactsController@deleteRelative')->name('contacts.relative.destroy');
    Route::post('contacts/{id?}/relatives/add', 'ContactsController@addRelative')->name('contacts.relatives.add');
    Route::post('contacts/relatives/update', 'ContactsController@updateRelative')->name('contacts.relatives.update');

    Route::get('contacts/{id?}/tags', 'ContactsController@tags')->name('contacts.tags');
    Route::get('contacts/{id?}/transactions', 'ContactsController@transactions')->name('contacts.transactions');
    Route::get('contacts/{id?}/recurring', 'ContactsController@recurringTransactions')->name('contacts.recurring');
    Route::get('contacts/{id?}/groups', 'ContactsController@groups')->name('contacts.groups');
    Route::get('contacts/{id?}/forms', 'ContactsController@forms')->name('contacts.forms');
    Route::get('contacts/{id?}/form/{entryId?}/entry', 'ContactsController@form')->name('contacts.form');
    Route::post('contacts/groupcontact', 'ContactsController@groupContact')->name('contacts.groupcontact');
    Route::post('contacts/tagcontact', 'ContactsController@tagContact')->name('contacts.tagcontact');
    Route::get('contacts/{id}/composer', 'ContactsController@composeEmail')->name('contacts.compose');
    Route::post('contacts/{id}/email', 'ContactsController@email')->name('contacts.email');
    Route::get('contacts/{id}/about', 'ContactsController@about')->name('contacts.about');
    Route::get('contacts/{id}/notes', 'ContactsController@notes')->name('contacts.notes');
    Route::put('contacts/{id}/about', 'ContactsController@updateAbout')->name('contacts.update.about');
    Route::put('contacts/{id}/child-checkin-note', 'ContactsController@updateChildCheckinNote')->name('contacts.update.child-checkin-note');
    Route::get('contacts/{id}/sms', 'ContactsController@composeSMS')->name('contacts.sms');
    Route::post('contacts/{id}/sms', 'ContactsController@sendSMS')->name('contacts.send.sms');
    Route::get('contacts/{id}/restore', 'ContactsController@showRestore')->name('contacts.restore.show');
    Route::post('contacts/{id}/restore', 'ContactsController@restore')->name('contacts.restore');
    Route::post('contacts/{id}/update-family', 'ContactsController@updateFamily')->name('contacts.update-family');
    Route::post('contacts/{id}/update-family-position', 'ContactsController@updateFamilyPosition')->name('contacts.update-family-position');
    Route::post('contacts/{id}/unsubscribed-phones', 'ContactsController@manageUnsubscribedPhones')->name('contacts.unsubscribed-phones');
    Route::post('contacts/{id}/resubscribe', 'ContactsController@resubscribe')->name('contacts.resubscribe');
    
    Route::resource('tasks', 'TasksController');

    Route::get('merge', 'MergeDataController@index')->name('merge.index');
    Route::get('merge/individual', 'MergeDataController@individual')->name('merge.individual');
    Route::get('merge/all', 'MergeDataController@all')->name('merge.all');

    Route::resource('addresses', 'AddressesController');
    Route::get('addresses/{id}/contact', 'AddressesController@create')->name('addresses.create');
    Route::get('groups/{id}/addresses/{addressId}', 'AddressesController@editGroupAddress')->name('groups.addresses.edit');

    Route::resource('tags', 'TagsController');
    Route::post('vue/create/tag', 'TagsController@vueCreateTag')->name('vue.create.tag');
    Route::get('tags/{id?}/contacts', 'TagsController@taggedContacts')->name('tags.contacts');

    Route::resource('folders', 'FoldersController');
    Route::resource('purposes', 'PurposesController');


    // Danny's PDF template routes foo only test
    Route::resource('templates', 'TemplatesController');
    Route::post('templates/upload-file', 'TemplatesController@uploadFile')->name('templates.fileupload');
    //end



    Route::get('purposes/{id}/transactions', 'PurposesController@transactions')->name('purposes.transactions');

    Route::resource('campaigns', 'CampaignsController');

    Route::resource('api', 'ApiController');

    Route::resource('integrations', 'IntegrationsController');
    Route::resource('recurring', 'RecurringTransactionsController', [
        'only' => ['index','show']
    ]);
    Route::get('recurring/filter/search', 'RecurringTransactionsController@search')->name('recurring.search');
    /*
    Route::resource('transactions', 'TransactionsController');
    Route::get('transactions/{search}/search', 'TransactionsController@search')->name('transactions.search');
     *
     */
    Route::post('transactions/import', 'TransactionSplitsController@importTransactions')->name('transactions.import');
    Route::post('transactions/parse-import', 'TransactionSplitsController@parseImport')->name('transactions.parse-import');
    Route::post('transactions/import-preview', 'TransactionSplitsController@importPreview')->name('transactions.import-preview');
    Route::resource('transactions', 'TransactionSplitsController');
    Route::get('transactions/{search}/search', 'TransactionSplitsController@search')->name('transactions.search');
    Route::post('transactions/{random}/export', 'TransactionSplitsController@export')->name('transactions.export');
    Route::get('my-transactions', 'TransactionSplitsController@myTransactions')->name('transactions.self');

    Route::resource('pledges', 'PledgesController');
    Route::get('pledges/{search}/search', 'PledgesController@search')->name('pledges.search');
    Route::get('pledges/stats/show', 'PledgesController@stats')->name('pledges.stats');
    Route::resource('pledgeforms', 'PledgeFormsController');

    /** NOTE Deprecated use 'statements' route instead */
    Route::resource('print-mail', 'Deprecated\StatementsController');
    // going away, but use of uuid here is deprecated, see below
    // Route::post('print-mail/{uuid}/settup', 'Deprecated\StatementsController@settupStatement')->name('print-mail.settup');
    // Route::get('print-mail/templates/get', 'Deprecated\StatementsController@getStatementTemplate')->name('print-mail.templates.get');
    // Route::get('print-mail/{uuid}/{id}/preview', 'Deprecated\StatementsController@previewStatement')->name('print-mail.preview');
    // Route::get('print-mail/{uuid}/{id}/print', 'Deprecated\StatementsController@printStatement')->name('print-mail.print');
    // Route::get('print-mail/{uuid}/{id}/pdf', 'Deprecated\StatementsController@pdfStatement')->name('print-mail.pdf');
    // Route::get('print-mail/{uuid}/{id}/report', 'Deprecated\StatementsController@reportStatement')->name('print-mail.report');

    // print-mail.settup doesn't appear to be in use
    Route::post('print-mail/{uuid}/settup', 'Deprecated\StatementsController@settupStatement')->name('print-mail.settup');
    Route::get('print-mail/templates/get', 'Deprecated\StatementsController@getStatementTemplate')->name('print-mail.templates.get');
    Route::get('print-mail/{id}/preview', 'Deprecated\StatementsController@previewStatement')->name('print-mail.preview');
    Route::get('print-mail/{id}/print', 'Deprecated\StatementsController@printStatement')->name('print-mail.print');
    Route::get('print-mail/{id}/pdf', 'Deprecated\StatementsController@pdfStatement')->name('print-mail.pdf');
    Route::get('print-mail/{id}/report', 'Deprecated\StatementsController@reportStatement')->name('print-mail.report');
    /** NOTE End deprecation */

    Route::get('groups/folders/{id}', 'GroupsController@showFolder')->name('groups.showFolder');
    Route::post('groups/search', 'GroupsController@search')->name('groups.search');
    Route::resource('groups', 'GroupsController');
    Route::get('groups/{id}/create', 'GroupsController@create')->name('groups.create');
    Route::get('groups/{id}/members', 'GroupsController@members')->name('groups.members');
    Route::get('groups/{id}/address', 'GroupsController@address')->name('groups.address');
    Route::get('groups/{id}/address/{aid}/edit', 'GroupsController@editAddress')->name('groups.editaddress');
    Route::post('groups/{id}/members/sync', 'GroupsController@sync')->name('groups.sync');
    Route::post('groups/{uuid}/members/sync-uuid', 'GroupsController@syncUuid')->name('groups.sync-uuid');
    Route::post('groups/{id}/join-self', 'JoinController@joinSelf')->name('groups.join-self');
    Route::get('groups/{id}/email', 'GroupsController@email')->name('groups.email');
    Route::get('groups/{id}/sms', 'GroupsController@sms')->name('groups.sms');
    Route::get('groups/{id}/excel', 'GroupsController@excel')->name('groups.excel');
    Route::get('groups/{id}/pdf-picture-directory', 'GroupsController@downloadPictureDirectory')->name('groups.pdf-picture-directory');

    /*** MAILCHIMP ROUTES ***/
    Route::get('integrations/{id}/mailchimp', 'Integration\MailchimpController@index')->name('mailchimp.index');
    Route::get('integrations/{id}/mailchimp/list', 'Integration\MailchimpController@addList')->name('mailchimp.addlist');
    Route::post('integrations/{id}/mailchimp/list', 'Integration\MailchimpController@storeList')->name('mailchimp.storelist');
    Route::get('integrations/{id}/mailchimp/{list}/members', 'Integration\MailchimpController@members')->name('mailchimp.members');
    Route::get('integrations/{id}/mailchimp/{list}/addmembers', 'Integration\MailchimpController@addMembers')->name('mailchimp.addmembers');
    Route::get('integrations/{id}/mailchimp/{list}/addtags', 'Integration\MailchimpController@addTags')->name('mailchimp.addtags');
    Route::get('integrations/{id}/mailchimp/{list}/deletetags', 'Integration\MailchimpController@deleteTags')->name('mailchimp.deletetags');
    Route::get('integrations/{id}/mailchimp/{list}/delete/{tagId}/tag', 'Integration\MailchimpController@unsubscribeTag')->name('mailchimp.unsubscribetag');
    Route::post('integrations/{id}/mailchimp/{list}/export', 'Integration\MailchimpController@store')->name('mailchimp.store');
    Route::get('integrations/{id}/mailchimp/{list}/delete/{member}', 'Integration\MailchimpController@unsubscribeContact')->name('mailchimp.unsubscribecontact');

    /*** CONTINUE TO GIVE ROUTES ***/
    Route::get('integrations/{id}/continuetogive', 'Integration\ContinueToGiveController@index')->name('continuetogive.index');

    Route::get('forms/paginate', 'FormsController@paginate');
    Route::resource('forms', 'FormsController');
    Route::get('forms/templates/{id}', 'FormsController@templates')->name('forms.templates');
    Route::get('forms/{id}/export', 'FormsController@export')->name('forms.export.entries');
    Route::get('forms/{id}/excel', 'FormsController@excel')->name('forms.export.excel');
    Route::post('forms/{id}/duplicate', 'FormsController@duplicate')->name('forms.duplicate');

    Route::resource('entries', 'FormEntriesController');
    Route::resource('calendars', 'CalendarsController');

    Route::resource('events', 'CalendarEventsController');
    Route::post('events/export/ics', 'CalendarEventsController@exportToIcs')->name('events.ics');
    Route::get('events/{id}/attenders', 'CalendarEventsController@attenders')->name('events.attenders');
    Route::get('events/{id}/report', 'CalendarEventsController@report')->name('events.report');
    Route::get('events/{id}/export/excel', 'CalendarEventsController@excel')->name('events.export.excel');

    Route::get('events/{id}/checkin', 'CalendarEventsController@checkin')->name('events.checkin');
    Route::get('events/{id}/autocheckin/{c}/{r}', 'CalendarEventsController@autocheckin')->name('events.autocheckin');
    Route::delete('events/{id}/uncheck/{contact}', 'CalendarEventsController@uncheck')->name('events.uncheck');
    Route::post('events/{id}/checkincontacts', 'CalendarEventsController@checkinContacts')->name('events.checkincontacts');
    Route::post('events/checkin/delete-ticket/{id}', 'CalendarEventsController@deleteTicket')->name('events.delete-ticket');
    Route::post('events/{id}/checkin-report', 'CalendarEventsController@checkinReport')->name('events.checkin-report');
    
    Route::get('events/{id}/alerts', 'CalendarEventsController@alerts')->name('events.alerts');
    Route::get('events/{id}/volunteers', 'CalendarEventsController@volunteers')->name('events.volunteers');
    Route::get('events/{id}/settings', 'CalendarEventsController@settings')->name('events.settings');

    Route::get('events/{id}/tickets/export', 'CalendarEventsController@exportTickets')->name('events.tickets.export');

    Route::resource('lists', 'ListsController');
    Route::get('lists/{id}/search', 'ListsController@search')->name('lists.search');
    Route::get('lists/{id}/composer', 'ListsController@composer')->name('lists.composer');
    Route::post('lists/{id}/email', 'ListsController@email')->name('lists.email');
    Route::get('lists/{id}/email/sent', 'ListsController@emailSent')->name('lists.email.sent');
    Route::get('lists/{list}/email/{email}/track', 'ListsController@emailTrack')->name('lists.email.track');
    Route::get('lists/{list}/email/{email}/track/{track}', 'ListsController@emailTrackHistory')->name('lists.email.track.history');

    Route::resource('crmreports', 'CRMReportsController');
    //old communications routes, not used anymore, see routes under prefix communications below
//    Route::resource('emails', 'EmailsController');
    Route::post('emails/{id}/preview', 'EmailsController@email')->name('emails.preview');
//    Route::get('emails/{id}/count', 'EmailsController@count')->name('emails.count');
//    Route::post('emails/{id}/count', 'EmailsController@storeNumberOfEmails')->name('emails.count.store');
//    Route::get('emails/{id}/exclude', 'EmailsController@selectTagsToExclude')->name('emails.exclude');
//    Route::post('emails/{id}/exclude', 'EmailsController@excludeTags')->name('emails.tags.exclude');
//    Route::get('emails/{id}/track', 'EmailsController@track')->name('emails.track');
//    Route::post('emails/{id}/track', 'EmailsController@storeTrack')->name('emails.track.store');
//    Route::get('emails/{id}/summary', 'EmailsController@getConfirm')->name('emails.getconfirm');
//    Route::post('emails/{id}/summary', 'EmailsController@postConfirm')->name('emails.postconfirm');
//    Route::get('emails/{id}/finish', 'EmailsController@finish')->name('emails.finish');

    Route::resource('notes', 'NotesController');
    Route::resource('tools', 'ToolsController');
    Route::get('tools/email/viewer', 'ToolsController@emailViewer')->name('tools.email.viewer');

    Route::group(['prefix' => 'ajax'], function(){
        Route::get( 'heartbeat', function() { return response()->json('alive'); } );
        Route::get('campaigns/chart', 'AjaxController@getChartFromCampaign')->name('ajax.get.chartfromcampaign');
        Route::post('contacts/autocomplete', 'AjaxController@contactsAutocomplete')->name('contacts.autocomplete');
        Route::get('contacts/timeline/{id}', 'AjaxController@contactsTimeline')->name('contacts.timeline');
        Route::post('contacts/timeline/view-email', 'AjaxController@viewEmail')->name('contacts.timeline.view-email');
        Route::post('contacts/timeline/view-print', 'Deprecated\StatementsController@viewPrint')->name('contacts.timeline.view-print');
        Route::get('contacts/payment/options', 'ContactsController@paymentOptions')->name('contacts.payment.options');

        Route::get('events/mobile/search/{id}', 'AjaxController@mobileContactsSearch')->name('mobile.contacts.search');
        Route::get('tags/get', 'AjaxController@tagsGetData')->name('tags.get');
        //Route::get('transactions/campaign/chartofaccount', 'AjaxController@campaignGetChartOfAccount')->name('transactions.campaign.chartofaccount');
        Route::get('oauth/token/show/{id}', 'AjaxController@oauthShowToken')->name('oauth.show.token');
        //routes for dashboard
        Route::post('widget/type/add', 'WidgetsController@add')->name('widget.type.add');
        Route::post('metrics/type/get', 'WidgetsController@getMetrics')->name('widget.metrics.type.get');
        Route::post('emails/{id}/tags', 'EmailsController@tags')->name('emails.tags');

        //Route::post('transactions/calendar/calculate/end/date', 'TransactionSplitsController@calendarCalculateEndDate')->name('transactions.calendar.calculate.end.date');
        Route::get('calendars/set/public', 'CalendarsController@setPublic')->name('calendars.set.public');
        Route::get('merge/view/duplicates', 'MergeDataController@ajaxViewDuplicates')->name('ajax.merge.view.duplicates');
        Route::get('merge/view/contact', 'MergeDataController@viewContact')->name('ajax.merge.view.contact');
        Route::get('merge/merge/duplicates', 'MergeDataController@ajaxMergeDuplicates')->name('ajax.merge.merge.duplicates');


        Route::get('tags', 'AjaxController@getTags')->name('ajax.tags.get');
        Route::post('tags', 'AjaxController@storeTag')->name('ajax.tags.store');

        Route::get('communications/{communication}/email/summary', 'Ajax\CommunicationsController@getEmailSummary')
        ->name('ajax.communications.emailsummary');
        Route::get('communications/{communication}/print/summary', 'Ajax\CommunicationsController@getPrintSummary')
        ->name('ajax.communications.printsummary');
        Route::post('communications/{communication}/email/send', 'Ajax\CommunicationsController@sendEmail')
        ->name('ajax.communications.sendemail');
        Route::post('communications/{communication}/print/track', 'Ajax\CommunicationsController@trackPrintedContacts')
        ->name('ajax.communications.trackprint');

        Route::resource('statementtemplate','Ajax\StatementTemplateController', [
            'only' => ['store', 'update', 'destroy'],
            'names' => [
                'update' => 'ajax.statementtemplate.update',
                'store' => 'ajax.statementtemplate.store',
                'destroy' => 'ajax.statementtemplate.destroy'
            ]
        ] );

        Route::get('statementtemplate/templatesmodal', 'Ajax\StatementTemplateController@loadTemplates')->name('ajax.statementtemplate.listmodal');

        Route::post('families/autocomplete', 'AjaxController@familiesAutocomplete')->name('families.autocomplete');
        
        Route::post('groups/autocomplete', 'AjaxController@groupsAutocomplete')->name('groups.autocomplete');
        
        Route::post('transactions/autocomplete', 'AjaxController@transactionsAutocomplete')->name('transactions.autocomplete');
        
        Route::post('errors','AjaxController@logError')->name('ajax.errors.store');
    });

    Route::group(['prefix' => 'communications/'], function(){
        // Additional communication routes
        Route::get('{id}/email/configure/{tab?}', 'CommunicationsController@configureEmail')->name('communications.configureemail');
        Route::get('{id}/print/configure/{tab?}', 'CommunicationsController@configurePrint')->name('communications.configureprint');

        Route::get('{id}/email/summary/{stat?}', 'CommunicationsController@emailSummary')->name('communications.emailsummary');
        Route::get('email/{email}/track/{track}', 'CommunicationsController@emailTrackHistory')->name('communications.email.track.history');
        Route::get('{id}/print/summary', 'CommunicationsController@printSummary')->name('communications.printsummary');

        // We are not using stripo editor
        //Route::get('stripo/getauthtoken', 'CommunicationsController@getStripoAuthToken')->name('stripo.getauthtoken');

        Route::post('testpdf', 'CommunicationsController@downloadTestPdf')->name('communications.testpdf');
        Route::post('{id}/cancel-send', 'CommunicationsController@cancelSend')->name('communications.cancel-send');

        // SMS
        Route::group(['prefix' => 'sms/'], function(){
            Route::get('track/history', 'SMSController@tracking')->name('sms.track.history');

            Route::get('phonenumber/showavailable', 'SMSController@showAvailablePhoneNumbers')->name('sms.showAvailablePhoneNumbers');
            Route::get('phonenumber/buy', 'SMSController@buyPhoneNumber')->name('sms.buyPhoneNumber');
            Route::post('test', 'SMSController@test')->name('sms.test');
            Route::post('send', 'SMSController@sendSms')->name('send.sms');
            Route::post('store', 'SMSController@storeInDatabase')->name('store.sms');
            Route::post('previewsummary', 'SMSController@previewSummary')->name('preview.summary');
            Route::post('get-texts-preview', 'SMSController@getTextConversationPreview')->name('sms.texts-preview');
            Route::post('get-texts', 'SMSController@getTexts')->name('sms.texts');
            Route::post('mark', 'SMSController@markSmsAsReadOrUnread')->name('sms.mark');
            Route::post('get-texts-scheduled', 'SMSController@getTextsScheduled')->name('sms.texts-scheduled');
            Route::post('view-schedule', 'SMSController@viewSchedule')->name('sms.view-schedule');
            Route::get('mass-text', 'SMSController@massText')->name('sms.mass-text');
        });
        Route::resource('sms', 'SMSController');
    });
    Route::resource('communications', 'CommunicationsController');


    /************** DATATABLES ****************/
    Route::group(['prefix' => 'search'], function(){
        Route::get('contacts', 'DataTables\ContactDataController@search')->name('search.contacts');
        Route::resource('contacts/state', 'DataTables\ContactDataController',[
            'only' => ['index','show','store','destroy'],
            'names' => [
                'index' => 'search.contacts.state.index',
                'show' => 'search.contacts.state.show',
                'store' => 'search.contacts.state.store',
                'destroy' => 'search.contacts.state.destroy'
            ],
        ]);
        Route::post('contacts/communication', 'DataTables\ContactDataController@storeCommunication')
        ->name('search.contacts.communication.store');
        Route::post('contacts/sms', 'DataTables\ContactDataController@storeSMS')
        ->name('search.contacts.sms.store');
        Route::get('contacts/{id}/pdf-picture-directory', 'DataTables\ContactDataController@downloadPictureDirectory')->name('search.contacts.pdf-picture-directory');
        Route::get('contacts/{id}/excel', 'DataTables\ContactDataController@excel')->name('search.contacts.excel');
        Route::post('contacts/{id}/totals', 'DataTables\ContactDataController@loadTotals')->name('search.contacts.totals');
    });



    Route::group(['prefix' => 'settings'], function(){
        Route::get('pledges', 'Settings\PledgeSettingsController@index')->name('settings.pledges.index');
        Route::post('pledges', 'Settings\PledgeSettingsController@store')->name('settings.pledges.store');
        Route::put('pledges/{id}', 'Settings\PledgeSettingsController@update')->name('settings.pledges.update');
        Route::resource('subscription', 'SubscriptionsController');
        Route::get('subscription/get/modules', 'SubscriptionsController@getModules')->name('subscription.modules');
        Route::get('subscription/check/info', 'SubscriptionsController@checkPaymentInfo');
        Route::get('subscription/payment/options', 'SubscriptionsController@paymentOptions');
        Route::post('subscription/save/credit/card', 'SubscriptionsController@saveCreditCardInfo');
        Route::put('subscription/update/payment/option', 'SubscriptionsController@updatePaymentOption')->name('subscription.update.payment.option');
        Route::put('subscription/delete/payment/option', 'SubscriptionsController@deletePaymentOption')->name('subscription.delete.payment.option');

        Route::get('subscription/invoices/info', 'SubscriptionsController@invoicesInfo')->name('subscription.invoices');
        Route::get('subscription/invoices/download/{id}', 'SubscriptionsController@downloadInvoice')->name('subscription.download.invoice');

        //sms
        //Route::get('sms', 'Settings\SMSSettingsController@index')->name('settings.sms.index');
        //Route::post('sms', 'Settings\SMSSettingsController@store')->name('settings.sms.store');

        Route::resource('sms', 'Settings\SMSSettingsController', [
            'names' => [
                'index' => 'settings.sms.index',
                'create' => 'settings.sms.create',
                'store' => 'settings.sms.store',
                'show' => 'settings.sms.show',
                'destroy' => 'settings.sms.destroy',
                'edit' => 'settings.sms.edit'
            ]
        ]);
        
        Route::resource('custom-fields', 'CustomFieldsController', [
            'names' => [
                'index' => 'settings.custom-fields.index',
                'create' => 'settings.custom-fields.create',
                'store' => 'settings.custom-fields.store',
                'update' => 'settings.custom-fields.update',
                'destroy' => 'settings.custom-fields.destroy'
            ]
        ]);
        Route::post('custom-fields/{id}/get', 'CustomFieldsController@getCustomFieldEditForm')->name('settings.custom-fields.get');
        Route::post('custom-fields/order', 'CustomFieldsController@saveOrder')->name('settings.custom-fields.save-order');
        Route::post('custom-fields/store-section', 'CustomFieldsController@storeSection')->name('settings.custom-fields.store-section');
        Route::post('custom-fields/section-order', 'CustomFieldsController@saveSectionOrder')->name('settings.custom-fields.save-section-order');
        Route::post('custom-fields/{id}/get-section', 'CustomFieldsController@getCustomFieldSectionEditForm')->name('settings.custom-fields.get-section');
        Route::put('custom-fields/{id}/update-section', 'CustomFieldsController@updateSection')->name('settings.custom-fields.update-section');
        Route::delete('custom-fields/{id}/destroy-section', 'CustomFieldsController@destroySection')->name('settings.custom-fields.destroy-section');
        
        /*** CCB ROUTES ***/
        Route::post('ccb/{id}/sync', 'CCBController@sync')->name('ccb.sync');
        Route::resource('ccb', 'CCBController', [
            'names' => [
                'index' => 'ccb.index',
                'create' => 'ccb.create',
                'store' => 'ccb.store',
                'show' => 'ccb.show',
                'destroy' => 'ccb.destroy'
            ]
        ]);
        
        /*** Bloomerang ROUTES ***/
        Route::post('bloomerang/{id}/sync', 'BloomerangController@sync')->name('bloomerang.sync');
        Route::resource('bloomerang', 'BloomerangController', [
            'names' => [
                'index' => 'bloomerang.index',
                'create' => 'bloomerang.create',
                'store' => 'bloomerang.store',
                'show' => 'bloomerang.show',
                'destroy' => 'bloomerang.destroy'
            ]
        ]);
        
        /*** Salesforce ROUTES ***/
        Route::post('salesforce/{id}/sync', 'SalesforceController@sync')->name('salesforce.sync');
        Route::resource('salesforce', 'SalesforceController', [
            'names' => [
                'index' => 'salesforce.index',
                'create' => 'salesforce.create',
                'store' => 'salesforce.store',
                'show' => 'salesforce.show',
                'destroy' => 'salesforce.destroy'
            ]
        ]);
        
        /*** Neon ROUTES ***/
        Route::post('neon/{id}/sync', 'NeonController@sync')->name('neon.sync');
        Route::resource('neon', 'NeonController', [
            'names' => [
                'index' => 'neon.index',
                'create' => 'neon.create',
                'store' => 'neon.store',
                'show' => 'neon.show',
                'destroy' => 'neon.destroy'
            ]
        ]);
    });
    
    Route::get('checkin/report/{group}', 'CheckinController@report')->name('checkin.report');
    Route::get('checkin/{group?}/{event?}', 'CheckinController@index')->name('checkin.index');
    Route::post('checkin/store', 'CheckinController@store')->name('checkin.store');
    Route::post('checkin/print', 'CheckinController@print')->name('checkin.print');
    Route::post('checkin/reprint', 'CheckinController@rePrint')->name('checkin.reprint');
    
    Route::post('documents/store', 'DocumentsController@store')->name('documents.store');
    Route::delete('documents/{uuid}/destroy', 'DocumentsController@destroy')->name('documents.destroy');
    Route::get('documents/{uuid}/download', 'DocumentsController@downloadDocument')->name('documents.download');
    
    Route::post('families/info', 'FamiliesController@familyInfo')->name('families.info');
    Route::resource('families', 'FamiliesController');
    Route::post('families/{id}/add-contact', 'FamiliesController@addContact')->name('families.add-contact');
});

/**
 * Accounting group
 */
Route::middleware(['auth', 'checkuid'])->prefix('accounting')->group(function () {
    Route::resource('accounts', 'AccountsController', [
        'except' => ['create', 'edit', 'show']
    ]);
    Route::post('accounts/sortorder', 'AccountsController@bulkUpdate');

    Route::resource('accountgroups', 'AccountGroupsController', [
        'except' => ['create', 'edit', 'show']
    ]);
    Route::post('accountgroups/sort/order', 'AccountGroupsController@bulkUpdate');

    Route::resource('sb', 'StartingBalancesController');
    Route::resource('registers', 'RegistersController');
    Route::get('registers/{id}/view_transaction', 'RegistersController@index');
    Route::get('registers/get/next/entry/number', 'RegistersController@getNextEntryNumber');
    Route::get('registers/get/debit/credit/titles', 'RegistersController@getCreditOrDebitTitles');

    Route::resource('journal-entries', 'JournalEntriesController');
    Route::get('journal-entries/fetch/data', 'JournalEntriesController@fetchData');

    Route::resource('bank-accounts', 'BankAccountsController', [
        'except' => ['create', 'edit', 'show']
    ]);
    Route::get('registergroups', 'BankAccountsController@getRegisterGroups');
    Route::get('bank-accounts/getBankData', 'BankAccountsController@getBankData');
    Route::post('bank-accounts/preview', 'BankAccountsController@preview');
    Route::post('bank-accounts/parseImport', 'BankAccountsController@parseImport');
    Route::post('bank-accounts/import_transactions', 'BankAccountsController@import_transactions');
    Route::post('bank/create-link-token', 'BankAccountsController@createLinkToken')->name('bank.link.create');
    Route::post('bank/update-link-token/{id}', 'BankAccountsController@updateLinkToken')->name('bank.link.update');
    Route::post('bank/link/accounts', 'BankAccountsController@linkAccount');
    Route::post('bank/unlink/account', 'BankAccountsController@unlinkRegister');
    Route::post('bank/transactions/map', 'BankAccountsController@mapAccountTransactions');
    Route::post('bank/sync/transactions', 'BankAccountsController@syncTransactions');
    Route::post('bank/stop/sync/transactions', 'BankAccountsController@stopSyncTransactions');

    Route::get('transactions', 'BankAccountsController@getAccountTransactions');
    // Route::get('accounts/test/data', 'BankAccountsController@getBankAccountsTestData');
    Route::post('transactions-bulk', 'BankAccountsController@mapAccountTransactionsBulk');

    Route::post('funds', 'AccountGroupsController@createFunds');
    Route::put('funds/{id}', 'AccountGroupsController@updateFund');

    // REPORTS
    Route::get('reports/list', 'ReportsController@index')->name('accounting.reports.index');
    Route::get('reports/compare-balance-sheet-by-fund', 'ReportsController@compareBalanceSheetByFund')->name('accounting.reports.compare-balance-sheet-by-fund');
    Route::get('reports/balance-sheet', 'ReportsController@balanceSheet')->name('accounting.reports.balance-sheet');
    Route::get('reports/income-statement', 'ReportsController@incomeStatement')->name('accounting.reports.income-statement');
    Route::post('reports/income-statement/{random}/export', 'ReportsController@exportIncomeStatement')->name('accounting.reports.income-statement.export');
    Route::get('reports/income-statement-by-month', 'ReportsController@incomeStatementByMonth')->name('reports.income.statement.bymonth');
    Route::get('reports/income-statement-by-fund', 'ReportsController@incomeStatementByFund')->name('reports.income.statement.byfund');
    Route::get('reports/bs-report-download', 'ReportsController@bsPdfDownload')->name('accounting.bs-report-download');
    Route::get('reports/is-report-download', 'ReportsController@isPdfDownload')->name('accounting.is-report-download');
    Route::get('reports/is-by-month-report-download', 'ReportsController@isByMonthPdfDownload')->name('accounting.is-by-month-report-download');
    Route::get('reports/is-by-fund-report-download', 'ReportsController@isByFundPdfDownload')->name('accounting.is-by-fund-report-download');

    Route::get('fund-transfers/journal/entries', 'JournalEntriesController@fundTransfers')->name('journal-entries.fund-transfers');
    Route::get('fund-transfers/journal/entries/create', 'JournalEntriesController@fundTransfersCreate')->name('journal-entries.fund-transfers.create');
    Route::post('fund-transfers', 'JournalEntriesController@fundTransfersStore')->name('journal-entries.fund-transfers-store');
    Route::delete('fund-transfers/{id}', 'JournalEntriesController@destroy')->name('journal-entries.destroy');
    //Route::get('registers/table', 'RegistersController@getTableData');
    Route::get('coming/soon', 'AccountsController@comingSoon')->name('accounting.coming.soon');
    Route::post('coming/soon', 'AccountsController@subscribeComingSoon')->name('accounting.subscribe.coming.soon');
    Route::get('bank-account-list', 'BankAccountsController@accountList')->name('bank.account.list');
    //Route::post('bank_get_access_token', 'BankAccountsController@getAccessToken');
    Route::post('bank_authorization_callback', 'BankAccountsController@bankAuthorizationCallback');
    Route::resource('bank', 'BankAccountsController');
    Route::post('sync-single-transaction', 'BankAccountsController@syncSingleTransaction');

    Route::group(['prefix' => 'ajax'], function() {
        Route::post('accounts/autocomplete', 'AccountsController@autocompleteAccounts');
        Route::post('funds/autocomplete', 'AccountGroupsController@autocompleteFunds');
        Route::get('transactions/accounting/linking', 'TransactionSplitsController@accountingLinking')->name('transactions.accounting.linking');

        Route::put('bank-transactions/{banktransaction}', 'Ajax\BankTransactionsController@update');
    });
});

/*------ End Auth protected routes --------*/

Route::group(['prefix' => 'ajax'], function(){
    Route::get('events/get/{id}', 'AjaxController@eventGetData')->name('events.getdata');
    Route::post('transactions/calendar/calculate/end/date', 'TransactionSplitsController@calendarCalculateEndDate')->name('transactions.calendar.calculate.end.date');
    Route::get('app/set/timezone', 'AjaxController@setTimezone')->name('app.set.timezone');
    Route::get('tickets/get/timeleft', 'AjaxController@ticketsGetTimeLeft')->name('tickets.get.timeleft');
});

/**
 * Tests Routes
 */
Route::get('developer/test', 'TestsController@test');
Route::get('developer/testCreateSalesmateActivity', 'TestsController@testCreateSalesmateActivity');
Route::get('developer/testChargeInvoice/{id}', 'TestsController@testChargeInvoice');
Route::get('developer/testReleaseTickets', 'TestsController@testReleaseTickets');
Route::get('developer/testSendSMS', 'TestsController@testSendSMS');
Route::get('developer/testSMSReply', 'TestsController@testSMSReply');
Route::get('developer/testBilling', 'TestsController@testBilling');
Route::get('developer/bank/reset-login/{id}', 'BankAccountsController@resetLogin');
Route::get('developer/testCheckinAlert', 'TestsController@testCheckinAlert');
Route::get('developer/create-families', 'TestsController@createFamilies');
Route::get('developer/send-email', 'TestsController@sendEmail');
Route::get('developer/track-email', 'TestsController@trackEmail');
Route::get('developer/send-sms', 'TestsController@sendSms');
Route::get('developer/check-trial', 'TestsController@checkTrial');
Route::get('developer/test-webhook', 'MailgunWebhooksController@testWebhook');
Route::get('developer/sync-purpose', 'TestsController@syncPurpose');
Route::get('developer/uploadTransactionsCECP', 'TestsController@uploadTransactionsCECP');
Route::get('developer/bill-clients', 'TestsController@billClients');
Route::resource('developer', 'TestsController');

Route::post('twilio/integration/track/webhook', 'SMSController@trackWebHook')->name('twilio.sms.track.webhook');
Route::post('twilio/integration/reply/webhook', 'SMSController@replyIncomeWebHook')->name('twilio.sms.reply.webhook');
Route::get('sms/reply/{id}', 'SMSController@reply')->name('twilio.sms.reply');
Route::post('sms/reply/{id}', 'SMSController@sendReply')->name('twilio.sms.send.reply');

Route::post('tiny/jwt', 'CommunicationsController@getTinyJwt')->name('tiny.jwt');

Route::middleware('auth')->get('/newMpAccount', 'DashboardController@newMpAccount')->name('newMpAccount');