<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
    
use App\Models\Settings\SettingValue;
use App\Traits\ModuleTrait;
use App\Traits\TokenTrait;
use App\Observers\TenantObserver;

class Tenant extends Model
{   
    use TokenTrait, ModuleTrait;
    
    public static function boot() {
        Tenant::observe(new TenantObserver());
    }
    
    /**
     * Gets contacts related
     * @return $mixed
     */
    public function contacts() {
        return $this->hasMany(Contact::class);
    }
    
    /**
     * Gets users related
     * @return $mixed
     */
    public function users() {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }
    
    /**
     * Gets roles related
     * @return $mixed
     */
    public function roles() {
        return $this->hasMany(Role::class);
    }
    
    /**
     * Gets folders that contains tags
     * @return $mixed
     */
    public function folders() {
        return $this->hasMany(TagFolder::class);
    }
    
    /**
     * Gets folders that contains tags
     * @return $mixed
     */
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->hasMany(Tag::class);
    }
    
    /**
     * Gets purposes
     * @return $mixed
     */
    public function purposes() {
        return $this->hasMany(Purpose::class);
    }

    public function account() {
        return $this->hasMany(Account::class);
    }

    public function funds() {
        return $this->hasMany(Fund::class);
    }

    public function accountGroups() {
        return $this->hasMany(AccountGroup::class);
    }

    public function startingBalances() {
        return $this->hasMany(StartingBlance::class);
    }
    
    /**
     * Gets campaigns
     * @return $mixed
     */
    public function campaigns() {
        return $this->hasMany(Campaign::class);
    }
    
    /**
     * Gets campaigns
     * @return $mixed
     */
    public function recurringTransactions() {
        return $this->hasMany(RecurringTransaction::class);
    }
    
    /**
     * Gets adresses
     * @return $mixed
     */
    public function addresses() {
        return $this->hasMany(Address::class);
    }
    
    /**
     * Overrides BaseModel::altIds, returning ALL the tenants alt_ids, including those related to other models
     */
    public function altIds() {
        return $this->hasMany(AltId::class);
    }
    
    public function altId() {
        return $this->hasOne(AltId::class)->where([
            'relation_id' => array_get($this, 'id'),
            'relation_type' => Tenant::class
        ]);
    }
    
    public function tokens() {
        //return $this->hasMany(TenantToken::class);
        return $this->hasMany(OauthAccessToken::class);
    }
    
    public function integrations() {
        return $this->hasMany(Integration::class);
    }
    
    public function paymentOptions() {
        return $this->hasMany(PaymentOption::class);
    }
    
    public function groups() {
        return $this->hasMany(Group::class);
    }
    
    public function forms() {
        return $this->hasMany(Form::class);
    }
    
    public function calendars() {
        return $this->hasMany(Calendar::class);
    }
    
    public function events() {
        return $this->hasMany(CalendarEvent::class);
    }
    
    public function transactionTemplates() {
        return $this->hasMany(TransactionTemplate::class);
    }
    public function transactionTemplateSplits() {
        return $this->hasMany(TransactionTemplateSplit::class);
    }
    
    /**
     * Gets transactions
     * @return $mixed
     */
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
    
    public function transactionSplits() {
        return $this->hasMany(TransactionSplit::class);
    }
    
    public function lists() {
        return $this->hasMany(Lists::class);
    }
    
    public function emails() {
        return $this->hasMany(Email::class);
    }
    
    public function emailsSent() {
        return $this->hasMany(EmailSent::class);
    }
    
    public function dashboard() {
        return $this->hasOne(Dashboard::class);
    }
    
    public function widgets() {
        return $this->hasMany(Widget::class);
    }
    
    public function customFields() {
        return $this->hasMany(CustomField::class);
    }
    
    public function customFieldValues() {
        return $this->hasMany(CustomFieldValue::class);
    }
    
    public function pledgeForms() {
        return $this->hasMany(PledgeForm::class);
    }
    
    public function settingValues() {
        return $this->hasMany(SettingValue::class);
    }
    
    public function statementTemplates() {
        return $this->hasMany(StatementTemplate::class);
    }
    
    public function statementTraking() {
        return $this->hasMany(StatementTracking::class);
    }
    
    public function notes() {
      return $this->hasMany(Note::class);
    }
    
    public function tasks() {
      return $this->hasMany(Task::class);
    }

    public function SMSPhoneNumbers(){
        return $this->hasMany(SMSPhoneNumber::class);
    }

    public function sms(){
        return $this->hasMany(SMSContent::class);
    }

    public function SMSSent(){
        return $this->hasMany(SMSSent::class);
    }

    public function modulesWithTrashed(){
        return $this->belongsToMany(Module::class, 'tenant_modules')->withTimestamps()->withPivot([
            'app_fee', 
            'phone_number_fee', 
            'sms_fee',
            'email_fee',
            'contact_fee',
            'start_billing_at',
            'next_billing_at',
            'last_billing_at',
            'discount_amount',
            'promo_code',
            'cancel',
            'cancelation_requested_at',
            'deleted_at',
            'reactivate_on_paid_invoice_id',
            'is_trial'
        ]);
    }
    
    public function modules()
    {
        return $this->modulesWithTrashed()->whereNull('tenant_modules.deleted_at');
    }
    
    public function modulesWithFee()
    {
        return $this->modules()
			->where(function($q) { 
				$q->where('tenant_modules.app_fee','>',0)
				->orWhere('tenant_modules.phone_number_fee','>',0)
				->orWhere('tenant_modules.sms_fee','>',0)
				->orWhere('tenant_modules.email_fee','>',0)
				->orWhere('tenant_modules.contact_fee','>',0); 
		});
    }
    
    public function modulesTrial()
    {
        return $this->modules()->where('is_trial', 1);
    }
    
    public function invoices(){
        return $this->hasMany(MPInvoice::class);
    }
    
    public function unpaidInvoices()
    {
        return $this->invoices()->unpaid();
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function bankInstitution()
    {
        return $this->hasMany(BankInstitution::class);
    }

    public function bankTransaction()
    {
        return $this->hasMany(BankTransaction::class);
    }
    
    
    public function scopeHasCRM($query) {
        return $query->whereHas('modules', function($m) {
            $m->where('id', 2);
        });
    }
    
    public function scopeNeedsCRM($query) {
        return $query->whereDoesntHave('modules', function($m) {
            $m->where('id', 2);
        });
    }
    
    public function scopeHasAccounting($query) {
        return $query->whereHas('modules', function($m) {
            $m->where('id', 3);
        });
    }
    
    public function scopeNeedsAccounting($query) {
        return $query->whereDoesntHave('modules', function($m) {
            $m->where('id', 3);
        });
    }
    
    public function getHasBloomerangAttribute()
    {
        return $this->integrations()->where('service', 'Bloomerang')->count() > 0;
    }
}
