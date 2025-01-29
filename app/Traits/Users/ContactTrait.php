<?php

namespace App\Traits\Users;
use App\Classes\MissionPillarsLog;
use App\Constants;
use App\Models\Contact;
use App\Models\EmailSent;
use App\Models\Address;
use App\Scopes\TenantScope;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 *
 * @author josemiguel
 */
trait ContactTrait {
    
    /**
     * Create a contact based on either the calling objects attributes or supplied values
     * @param  [ArrayAccess] $values  Optional. If provided, all attributes are mapped to newly created contact. Otherwise, name (mapped to first_name), last_name, email (email_1), id (user_id), and tenant_id are mapped to new contact
     * @return [App\Models\Contact]         The created contact
     */
    public function createContact($values = null) {
        if (!$values) { 
            $values = [];
            // using an object that applies this trait, only use these attributes
            array_set($values, 'first_name', array_get($this, 'name'));
            array_set($values, 'last_name', array_get($this, 'last_name'));
            array_set($values, 'email_1', array_get($this, 'email'));
            array_set($values, 'user_id', array_get($this, 'id'));
            array_set($values, 'tenant_id', array_get($this, 'tenant_id'));
        } // else otherwise use all passed in attributes
        if (!array_get($values, 'tenant_id')) array_set($values, 'tenant_id', $this->tenant_id);
        $contact = mapModel(new Contact(), $values); // preferred over Contact::create([]); see Contact::fillable 
        
        $orig_use_tenant_id = TenantScope::useTenantId() ?: false;
        TenantScope::useTenantId(array_get($values, 'tenant_id'));
        $contact->save();
        $contact->refresh();
        TenantScope::useTenantId($orig_use_tenant_id);
        
        return $contact;
    }
    
    public function sendEmail($email) {
        $sent = new EmailSent();
        array_set($sent, 'contact_id', array_get($this, 'id'));
        array_set($sent, 'email_content_id', array_get($email, 'id'));
        array_set($sent, 'sent_at', Carbon::now());
        array_set($sent, 'message', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.QUEUED'));
        array_set($sent, 'tenant_id', array_get($this, 'tenant_id'));
        $sent->save();
        //auth()->user()->tenant->emailsSent()->save($sent);
    }
    
    /**
     * Assign model properties values based on excel template
     * @param Excel $row
     */
    public function assignFromExcelRow($row) {
        $names = explode(' ', array_get($row, 'first_name'));
        
        if(count($names) > 1 && is_null(array_get($row, 'last_name'))){
            array_set($this, 'first_name', $names[0]);
            if( is_null(array_get($row, 'last_name')) ){
                try {
                    array_set($this, 'last_name', $names[1]);
                } catch (\ErrorException $ex) {
                    //dd($ex->getMessage());
                    MissionPillarsLog::log([
                        'event' => 'excel',
                        'caller_function' => implode('.', [self::class, __FUNCTION__]),
                        'code' => $ex->getCode(),
                        'message' => $ex->getMessage()
                    ]);
                }
                
            }
        }
        else{
            if (!empty(array_get($row, 'first_name'))) {
                array_set($this, 'first_name', array_get($row, 'first_name'));
            }
            if (!empty(array_get($row, 'last_name'))) {
                array_set($this, 'last_name', array_get($row, 'last_name'));
            }
        }
        
        if( is_null(array_get($row, 'preferred_name')) && (!empty(array_get($row, 'first_name')) || !empty(array_get($row, 'last_name'))) ){
            array_set($this, 'preferred_name', array_get($row, 'first_name').' '.array_get($row, 'last_name'));
        }
        elseif (!empty(array_get($row, 'preferred_name'))) {
            array_set($this, 'preferred_name', array_get($row, 'preferred_name'));
        }
        
        if (!empty(array_get($row, 'middle_name'))) {
            array_set($this, 'middle_name', array_get($row, 'middle_name'));
        }
        if (!empty(array_get($row, 'birth_date'))) {
            array_set($this, 'dob', array_get($row, 'birth_date'));
        }
        if (!empty(array_get($row, 'email'))) {
            array_set($this, 'email_1', array_get($row, 'email'));
        }
        if (!empty(array_get($row, 'cell_phone'))) {
            array_set($this, 'cell_phone', array_get($row, 'cell_phone'));
        }
        if (!empty(array_get($row, 'gender'))) {
            array_set($this, 'gender', array_get($row, 'gender'));
        }
        if (!empty(array_get($row, 'marital_status'))) {
            array_set($this, 'marital_status', array_get($row, 'marital_status'));
        }
        if (!empty(array_get($row, 'position'))) {
            array_set($this, 'position', array_get($row, 'position'));
        }
        if (!empty(array_get($row, 'organization'))) {
            array_set($this, 'company', array_get($row, 'organization'));
        }
        if (!empty(array_get($row, 'website'))) {
            array_set($this, 'website', array_get($row, 'website'));
        }
        
        if (!empty(array_get($row, 'work_phone'))) {
            array_set($this, 'work_phone', array_get($row, 'work_phone'));
        }
        if (!empty(array_get($row, 'salutation'))) {
            array_set($this, 'salutation', array_get($row, 'salutation'));
        }
        if (!empty(array_get($row, 'source'))) {
            array_set($this, 'source', array_get($row, 'source'));
        }
        if (!empty(array_get($row, 'facebook'))) {
            array_set($this, 'facebook', array_get($row, 'facebook'));
        }
        if (!empty(array_get($row, 'facebook_id'))) {
            array_set($this, 'facebook_id', array_get($row, 'facebook_id'));
        }
        if (!empty(array_get($row, 'twitter'))) {
            array_set($this, 'twitter', array_get($row, 'twitter'));
        }
        if (!empty(array_get($row, 'death_date'))) {
            array_set($this, 'death_date', dbDateFormat(array_get($row, 'death_date')));
        }
    }

    public function setAddressFromExcelRow($row){
        $address = $this->addressInstance()->first();
        if(is_null($address)){
            $address = new Address();
        }

        if (!empty(array_get($row, 'address'))) {
            array_set($address, 'mailing_address_1', array_get($row, 'address'));
        }
        if (!empty(array_get($row, 'city'))) {
            array_set($address, 'city', array_get($row, 'city'));
        }
        if (!empty(array_get($row, 'region'))) {
            array_set($address, 'region', array_get($row, 'region'));
        }
        if (!empty(array_get($row, 'country'))) {
            array_set($address, 'country', array_get($row, 'country'));
        }
        if (!empty(array_get($row, 'postal_code'))) {
            array_set($address, 'postal_code', array_get($row, 'postal_code'));
        }
        array_set($address, 'relation_id', array_get($this, 'id'));
        array_set($address, 'relation_type', get_class($this));
        try {
            auth()->user()->tenant->addresses()->save($address);
        } catch (\ErrorException $ex) {
            MissionPillarsLog::log([
                'event' => 'excel',
                'caller_function' => implode('.', [self::class, __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ]);
        }
        catch (\Illuminate\Database\QueryException $ex) {
            MissionPillarsLog::log([
                'event' => 'excel',
                'caller_function' => implode('.', [self::class, __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ]);
        }
    }
    
    public function timeline($page) {
      $total = 10;
      $offset = ($page - 1) * $total;
      $contactid = array_get($this, 'id');
      $tenantid = array_get(auth()->user(), 'tenant_id');
      
      $query = "select * from (
select 'transaction' as timeline_category,
t.transaction_initiated_at as timeline_date,
t.status as timeline_status,
ts.amount as timeline_amount,
coa.name as timeline_chart_of_account,
c.name as timeline_campaign,
null as timeline_subject,
null as timeline_group,
t.id as timeline_id
from transactions t
inner join transaction_splits ts on ts.transaction_id = t.id
inner join purposes coa on coa.id = ts.purpose_id
inner join campaigns c on c.id = ts.campaign_id
where t.contact_id = $contactid
and t.tenant_id = $tenantid
and t.deleted_at IS NULL
union

select 'email_sent' as timeline_category,
if(ec.time_scheduled > es.sent_at and es.sent = 0, ec.time_scheduled, es.sent_at) as timeline_date,
es.status as timeline_status,
0 as timeline_amount,
null as timeline_chart_of_account,
null as timeline_campaign,
ifnull(cc.subject, ec.subject) as timeline_subject,
es.communication_content_id as timeline_group,
es.id as timeline_id
from email_sent es
inner join email_content ec on ec.id = es.email_content_id
left join communication_contents cc on cc.id = es.communication_content_id
where es.contact_id = $contactid
and es.tenant_id = $tenantid
and es.deleted_at IS NULL
union

select 'printout' as timeline_category,
cc.created_at as timeline_date,
'Printout' as timeline_status,
0 as timeline_amount,
null as timeline_chart_of_account,
null as timeline_campaign,
ifnull(cc2.subject, c.label) as timeline_subject,
cc.communication_content_id as timeline_group,
cc.id as timeline_id
from communication_contact cc
inner join communications c on c.id = cc.communication_id
left join communication_contents cc2 on cc2.id = cc.communication_content_id
where cc.contact_id = $contactid
and c.tenant_id = $tenantid
and c.deleted_at IS NULL
union

select 'group' as timeline_category,
gc.created_at as timeline_date,
'complete' as timeline_status,
0 as timeline_amount,
null as timeline_chart_of_account,
null as timeline_campaign,
null as timeline_subject,
g.name as timeline_group,
g.id as timeline_id
from groups g
inner join group_contact gc on gc.contact_id = $contactid and gc.group_id = g.id
where g.tenant_id = $tenantid
and g.deleted_at IS NULL
union

select 'event' as timeline_category,
ccr.created_at as timeline_date,
'complete' as timeline_status,
0 as timeline_amount,
cet.name as timeline_chart_of_account,
null as timeline_campaign,
null as timeline_subject,
null as timeline_group,
cet.id as timeline_id
from calendar_event_contact_register ccr
inner join calendar_event_template_splits cts on cts.id = ccr.calendar_event_template_split_id
inner join calendar_event_templates cet on cet.id = cts.calendar_event_template_id
where ccr.contact_id = $contactid and ccr.tenant_id = $tenantid
union

select 'checkin' as timeline_category,
pt.updated_at as timeline_date,
'checkin' as timeline_status,
0 as timeline_amount,
cet.name as timeline_chart_of_account,
null as timeline_campaign,
null as timeline_subject,
null as timeline_group,
ccr.id as timeline_id
from purchased_tickets pt
inner join calendar_event_contact_register ccr on ccr.id = pt.calendar_event_contact_register_id
inner join calendar_event_template_splits cts on cts.id = ccr.calendar_event_template_split_id
inner join calendar_event_templates cet on cet.id = cts.calendar_event_template_id
where pt.checked_in = true and ccr.contact_id = $contactid and ccr.tenant_id = $tenantid
and pt.deleted_at IS NULL
union

select 'task' as timeline_category,
t.created_at as timeline_date,
t.status as timeline_status,
t.due as timeline_amount,
t.name as timeline_chart_of_account,
null as timeline_campaign,
t.completed_at as timeline_subject,
null as timeline_group,
t.id as timeline_id
from tasks t
where t.linked_to = $contactid
and t.tenant_id = $tenantid
and t.deleted_at IS NULL
union

select 'note' as timeline_category,
n.date as timeline_date,
'complete' as timeline_status,
null as timeline_amount,
n.title as timeline_chart_of_account,
null as timeline_campaign,
n.content as timeline_subject,
null as timeline_group,
n.id as timeline_id
from notes n
where n.relation_id = $contactid
and n.relation_type = '". addslashes(Contact::class)."'
and n.tenant_id = $tenantid
and n.deleted_at IS NULL
union

select 'form' as timeline_category,
fe.created_at as timeline_date,
'complete' as timeline_status,
null as timeline_amount,
f.name as timeline_chart_of_account,
null as timeline_campaign,
null as timeline_subject,
null as timeline_group,
f.id as timeline_id
from forms f
inner join form_entries fe on fe.form_id = f.id
where fe.contact_id = $contactid
and fe.tenant_id = $tenantid
and f.deleted_at IS NULL
union

select 'ticket' as timeline_category,
pt.created_at as timeline_date,
'complete' as timeline_status,
pt.price as timeline_amount,
pt.ticket_name as timeline_chart_of_account,
cet.name as timeline_campaign,
cet.is_paid as timeline_subject,
null as timeline_group,
pt.id as timeline_id
from purchased_tickets pt
inner join calendar_event_contact_register cecr on cecr.id = pt.calendar_event_contact_register_id
inner join calendar_event_template_splits cets on cets.id = cecr.calendar_event_template_split_id
inner join calendar_event_templates cet on cet.id = cets.calendar_event_template_id
where cecr.contact_id = $contactid
and cecr.tenant_id = $tenantid
and pt.deleted_at IS NULL
union

select 'sms_sent' as timeline_category,
es.sent_at as timeline_date,
es.status as timeline_status,
es.id as timeline_amount,
null as timeline_chart_of_account,
ec.sms_phone_number_to as timeline_campaign,
ec.content as timeline_subject,
ec.sms_phone_number_from as timeline_group,
es.id as timeline_id
from sms_sent es
inner join sms_content ec on ec.id = es.sms_content_id
where es.deleted_at IS NULL and (es.to_contact_id = $contactid
or (es.from_contact_id = $contactid and es.created_by is null)
and es.tenant_id = $tenantid)
group by es.sms_content_id
) as timeline order by timeline_date desc, timeline_id desc limit $offset, $total";
      
      $result = DB::select(DB::raw($query));
      
      $timeline = array_map(function($value){
        return (array)$value;
      }, $result);
      return $timeline;
    }
}
