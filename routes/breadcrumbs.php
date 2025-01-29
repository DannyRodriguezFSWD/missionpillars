<?php

Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('dashboard.index'));
});

Breadcrumbs::register('help', function ($breadcrumbs) {
    $breadcrumbs->push('Help', route('dashboard.help'));
});

Breadcrumbs::register('contacts', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Contacts', route('contacts.index'));
});

Breadcrumbs::register('contacts.create', function ($breadcrumbs) {
    $breadcrumbs->parent('contacts');
    $breadcrumbs->push('Create', route('contacts.create'));
});

Breadcrumbs::register('contacts.edit', function ($breadcrumbs,$contact) {
    $breadcrumbs->parent('contacts.show', $contact);
    $breadcrumbs->push('Edit', route('contacts.edit', $contact));
});

Breadcrumbs::register('contacts.show', function ($breadcrumbs,$contact) {
    $breadcrumbs->parent('contacts');
    $breadcrumbs->push($contact->full_name, route('contacts.show',$contact));
});

Breadcrumbs::register('contacts.send.sms', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('contacts.show', $contact);
    $breadcrumbs->push('SMS', route('contacts.send.sms', $contact));
});

Breadcrumbs::register('contacts.relatives', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('contacts.show', $contact);
    $breadcrumbs->push(__('Relatives'), route('contacts.relatives', $contact));
});

Breadcrumbs::register('contacts.notes', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('contacts.show', $contact);
    $breadcrumbs->push(__('Notes'), route('contacts.notes', $contact));
});

Breadcrumbs::register('contacts_advance_search', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Contacts', route('search.contacts'));
});

Breadcrumbs::register('contacts.directory', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Picture Directory', route('contacts.directory'));
});

Breadcrumbs::register('import_contacts', function ($breadcrumbs) {
    $breadcrumbs->parent('contacts');
    $breadcrumbs->push('Import', route('contacts.import'));
});

Breadcrumbs::register('merge_contacts', function ($breadcrumbs) {
    $breadcrumbs->parent('contacts');
    $breadcrumbs->push('Merge', route('merge.index'));
});

Breadcrumbs::register('merge_individual_contacts', function ($breadcrumbs) {
    $breadcrumbs->parent('merge_contacts');
    $breadcrumbs->push('Merge Individual', route('merge.individual'));
});

Breadcrumbs::register('contacts.restore', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('contacts');
    $breadcrumbs->push($contact->full_name, route('contacts.restore.show', $contact));
});

Breadcrumbs::register('contacts.edit-profile', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($contact->full_name, route('contacts.edit-profile', $contact));
});

Breadcrumbs::register('contacts.tags', function ($breadcrumbs, $contact) {
    $breadcrumbs->parent('contacts.show', $contact);
    $breadcrumbs->push('Tags', route('contacts.tags', $contact));
});

Breadcrumbs::register('transactions.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Transactions', route('transactions.index'));
});

Breadcrumbs::register('recurring.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Recurring Transactions', route('recurring.index'));
});

Breadcrumbs::register('recurring.show', function ($breadcrumbs,$template) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Recurring Transaction', route('recurring.show',$template));
});

Breadcrumbs::register('pledges.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Pledges', route('pledges.index'));
});

Breadcrumbs::register('pledges.create', function ($breadcrumbs) {
    $breadcrumbs->parent('pledges.index');
    $breadcrumbs->push('Create', route('pledges.create'));
});

Breadcrumbs::register('pledges.show', function ($breadcrumbs,$template) {
    $breadcrumbs->parent('pledges.index');
    $breadcrumbs->push('Pledge', route('pledges.show',$template));
});

Breadcrumbs::register('pledges.edit', function ($breadcrumbs,$template) {
    $breadcrumbs->parent('pledges.index');
    $breadcrumbs->push('Edit', route('pledges.edit',$template));
});

Breadcrumbs::register('pledgeforms.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Pledge Forms', route('pledgeforms.index'));
});

Breadcrumbs::register('pledgeforms.create', function ($breadcrumbs) {
    $breadcrumbs->parent('pledgeforms.index');
    $breadcrumbs->push('Create', route('pledgeforms.create'));
});

Breadcrumbs::register('pledgeforms.edit', function ($breadcrumbs,$form) {
    $breadcrumbs->parent('pledgeforms.index');
    $breadcrumbs->push('Edit', route('pledgeforms.edit',$form));
});

Breadcrumbs::register('pledgeforms.show', function ($breadcrumbs,$form) {
    $breadcrumbs->parent('pledgeforms.index');
    $breadcrumbs->push($form->name, route('pledgeforms.show',$form));
});

Breadcrumbs::register('search.contacts.state.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Saved Search', route('search.contacts.state.index'));
});

Breadcrumbs::register('communications.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Communications', route('communications.index'));
});

Breadcrumbs::register('communications.create', function ($breadcrumbs) {
    $breadcrumbs->parent('communications.index');
    $breadcrumbs->push('Create', route('communications.create'));
});

Breadcrumbs::register('communications.show', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.index');
    $breadcrumbs->push($comm->subject ? $comm->subject : $comm->label, route('communications.show',$comm));
});

Breadcrumbs::register('communications.edit', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.index');
    $breadcrumbs->push($comm->subject ? $comm->subject : $comm->label, route('communications.edit',$comm));
});

Breadcrumbs::register('communications.configure', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.edit', $comm);
    $breadcrumbs->push('Configure');
});

Breadcrumbs::register('communications.emailsummary', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.edit', $comm);
    $breadcrumbs->push('Email Summary',route('communications.emailsummary',$comm));
});

Breadcrumbs::register('communications.email.tracking', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.emailsummary', $comm);
    $breadcrumbs->push('Tracking');
});

Breadcrumbs::register('communications.printsummary', function ($breadcrumbs,$comm) {
    $breadcrumbs->parent('communications.edit', $comm);
    $breadcrumbs->push('Print Summary');
});

Breadcrumbs::register('sms.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Texts', route('sms.index'));
});

Breadcrumbs::register('sms.create', function ($breadcrumbs) {
    $breadcrumbs->parent('sms.index');
    $breadcrumbs->push('Create', route('sms.create'));
});

Breadcrumbs::register('sms.show', function ($breadcrumbs,$sms) {
    $breadcrumbs->parent('sms.index');
    $breadcrumbs->push('Show', route('sms.show',$sms));
});

Breadcrumbs::register('groups.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Small Groups', route('groups.index'));
});

Breadcrumbs::register('groups.create', function ($breadcrumbs, $folder) {
    $breadcrumbs->parent('groups.index');
    $breadcrumbs->push('Create', route('groups.create', $folder));
});

Breadcrumbs::register('groups.show', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups.index');
    $breadcrumbs->push($group->name, route('groups.show', $group));
});

Breadcrumbs::register('groups.edit', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('groups.show', $group);
    $breadcrumbs->push('Edit', route('groups.edit', $group));
});

Breadcrumbs::register('events.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Events', route('events.index'));
});

Breadcrumbs::register('events.create', function ($breadcrumbs) {
    $breadcrumbs->parent('events.index');
    $breadcrumbs->push('Create', route('events.create'));
});

Breadcrumbs::register('events.settings', function ($breadcrumbs,$event) {
    $breadcrumbs->parent('events.index');
    $breadcrumbs->push($event->name, route('events.settings',$event));
});

Breadcrumbs::register('events.checkin', function ($breadcrumbs, $event) {
    $breadcrumbs->parent('events.index');
    $breadcrumbs->push($event->name, route('events.checkin', $event));
});

Breadcrumbs::register('child-checkin.about', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Child Checkin', route('child-checkin.about'));
});

Breadcrumbs::register('forms.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Forms', route('forms.index'));
});

Breadcrumbs::register('forms.create', function ($breadcrumbs) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push('Create', route('forms.create'));
});

Breadcrumbs::register('forms.edit', function ($breadcrumbs,$form) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push($form->name, route('forms.edit',$form));
});

Breadcrumbs::register('forms.show', function ($breadcrumbs,$form) {
    $breadcrumbs->parent('forms.index');
    $breadcrumbs->push($form->name, route('forms.show',$form));
});

Breadcrumbs::register('registers.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Accounting Transactions', route('registers.index'));
});

Breadcrumbs::register('journal-entries.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Journal Entries', route('journal-entries.index'));
});

Breadcrumbs::register('journal-entries.fund-transfers', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Journal Entries Fund Transfers', route('journal-entries.fund-transfers'));
});

Breadcrumbs::register('accounts.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Accounting Accounts', route('accounts.index'));
});

Breadcrumbs::register('sb.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Accounting Starting Balances', route('sb.index'));
});

Breadcrumbs::register('bank-accounts.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Accounting Bank Integration', route('bank-accounts.index'));
});

Breadcrumbs::register('crmreports.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('CRM Reports', route('crmreports.index'));
});

Breadcrumbs::register('crmreports.show', function ($breadcrumbs,$report) {
    $breadcrumbs->parent('crmreports.index');
    $breadcrumbs->push(array_get($report, 'name'), route('crmreports.show',$report));
});

Breadcrumbs::register('accounting.reports.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Accounting Reports', route('accounting.reports.index'));
});

Breadcrumbs::register('accounting.reports.compare-balance-sheet-by-fund', function ($breadcrumbs) {
    $breadcrumbs->parent('accounting.reports.index');
    $breadcrumbs->push('Compare Balance Sheet Fund', route('accounting.reports.compare-balance-sheet-by-fund'));
});

Breadcrumbs::register('accounting.reports.balance-sheet', function ($breadcrumbs) {
    $breadcrumbs->parent('accounting.reports.index');
    $breadcrumbs->push('Balance Sheet', route('accounting.reports.balance-sheet'));
});

Breadcrumbs::register('accounting.reports.income-statement', function ($breadcrumbs) {
    $breadcrumbs->parent('accounting.reports.index');
    $breadcrumbs->push('Income Statement', route('accounting.reports.income-statement'));
});

Breadcrumbs::register('reports.income.statement.bymonth', function ($breadcrumbs) {
    $breadcrumbs->parent('accounting.reports.index');
    $breadcrumbs->push('Income Statement By Month', route('reports.income.statement.bymonth'));
});

Breadcrumbs::register('reports.income.statement.byfund', function ($breadcrumbs) {
    $breadcrumbs->parent('accounting.reports.index');
    $breadcrumbs->push('Income Statement By Fund', route('reports.income.statement.byfund'));
});

Breadcrumbs::register('purposes.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Purposes', route('purposes.index'));
});

Breadcrumbs::register('purposes.create', function ($breadcrumbs) {
    $breadcrumbs->parent('purposes.index');
    $breadcrumbs->push('Create', route('purposes.create'));
});

Breadcrumbs::register('purposes.show', function ($breadcrumbs,$chart) {
    $breadcrumbs->parent('purposes.index');
    $breadcrumbs->push($chart->name, route('purposes.show',$chart));
});

Breadcrumbs::register('purposes.edit', function ($breadcrumbs,$chart) {
    $breadcrumbs->parent('purposes.show',$chart);
    $breadcrumbs->push('Edit', route('purposes.edit',$chart));
});

Breadcrumbs::register('campaigns.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Fundraisers', route('campaigns.index'));
});

Breadcrumbs::register('campaigns.create', function ($breadcrumbs) {
    $breadcrumbs->parent('campaigns.index');
    $breadcrumbs->push('Create', route('campaigns.create'));
});

Breadcrumbs::register('campaigns.show', function ($breadcrumbs,$campaign) {
    $breadcrumbs->parent('campaigns.index');
    $breadcrumbs->push($campaign->name, route('campaigns.show',$campaign));
});

Breadcrumbs::register('campaigns.edit', function ($breadcrumbs,$campaign) {
    $breadcrumbs->parent('campaigns.show',$campaign);
    $breadcrumbs->push('Edit', route('campaigns.edit',$campaign));
});

Breadcrumbs::register('users.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Users', route('users.index'));
});

Breadcrumbs::register('users.create', function ($breadcrumbs) {
    $breadcrumbs->parent('users.index');
    $breadcrumbs->push('Create', route('users.create'));
});

Breadcrumbs::register('users.show', function ($breadcrumbs,$user) {
    $breadcrumbs->parent('users.index');
    $breadcrumbs->push($user->full_name, route('users.show',$user));
});

Breadcrumbs::register('users.edit', function ($breadcrumbs,$user) {
    $breadcrumbs->parent('users.show',$user);
    $breadcrumbs->push('Edit', route('users.edit',$user));
});

Breadcrumbs::register('roles.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Roles', route('roles.index'));
});

Breadcrumbs::register('roles.create', function ($breadcrumbs) {
    $breadcrumbs->parent('roles.index');
    $breadcrumbs->push('Create', route('roles.create'));
});

Breadcrumbs::register('roles.show', function ($breadcrumbs, $role) {
    $breadcrumbs->parent('roles.index');
    $breadcrumbs->push($role->display_name, route('roles.show', $role));
});

Breadcrumbs::register('roles.edit', function ($breadcrumbs,$role) {
    $breadcrumbs->parent('roles.show',$role);
    $breadcrumbs->push('Edit', route('roles.edit',$role));
});

Breadcrumbs::register('tags.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Tags', route('tags.index'));
});

Breadcrumbs::register('settings.pledges.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Pledge Settings', route('settings.pledges.index'));
});

Breadcrumbs::register('settings.sms.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('SMS Settings', route('settings.sms.index'));
});

Breadcrumbs::register('settings.sms.create', function ($breadcrumbs) {
    $breadcrumbs->parent('settings.sms.index');
    $breadcrumbs->push('Purchase Phone Number', route('settings.sms.create'));
});

Breadcrumbs::register('settings.sms.edit', function ($breadcrumbs, $smsPhoneNumber) {
    $breadcrumbs->parent('settings.sms.index');
    $breadcrumbs->push($smsPhoneNumber->name_and_number, route('settings.sms.edit', $smsPhoneNumber));
});

Breadcrumbs::register('subscription.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Subscription Plan Details', route('subscription.index'));
});

Breadcrumbs::register('subscription.invoices', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Subscription Invoices', route('subscription.invoices'));
});

Breadcrumbs::register('subscription.show', function ($breadcrumbs,$seg) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Subscription Payment Method', route('subscription.show',$seg));
});

Breadcrumbs::register('tools.email.viewer', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Email Viewer', route('tools.email.viewer'));
});

Breadcrumbs::register('tenants.edit', function ($breadcrumbs, $tenant) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Edit Organization Info', route('tenants.edit', $tenant));
});

Breadcrumbs::register('integrations.show', function ($breadcrumbs, $integration) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($integration->service, route('integrations.show', $integration));
});

Breadcrumbs::register('settings.custom-fields.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Custom Fields', route('settings.custom-fields.index'));
});

Breadcrumbs::register('tasks', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Tasks', route('tasks.index'));
});
