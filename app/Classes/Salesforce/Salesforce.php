<?php

namespace App\Classes\Salesforce;

use App\Models\Address;
use App\Models\AltId;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Family;
use App\Models\PaymentOption;
use App\Models\Purpose;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Traits\AlternativeIdTrait;

class Salesforce extends SalesforceAPI
{
    use AlternativeIdTrait;
    
    private $take = 200; // Cannot take more than 200 at a time
    
    public function sync($date)
    {
        ini_set('max_execution_time', 600);
        
//        $this->syncHouseholds($date);
//        $this->syncContacts($date);
//        $this->syncOrganizations($date);
//        $this->syncRelationships($date);
//        $this->syncCampaigns($date);
//        $this->syncTransactions($date);
        
        dd('done');
    }
    
    public function syncHouseholds($date)
    {
        $total = $this->getHouseholds(null, 0, $date);
        $totalHouseholds = array_get($total, 'totalSize');
        
        if ($totalHouseholds === 0) {
            return 'No new households';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalHouseholds; $i+=$this->take) {
            $households = $this->getHouseholds($start, $this->take, $date);
            
            if (array_get($households, 'totalSize') > 0) {
                foreach (array_get($households, 'records') as $household) {
                    $this->importHousehold($household);
                    $start = array_get($household, 'Id');
                }
            }
        }
    }
    
    public function importHousehold($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // do not get deleted
        if (array_get($data, 'IsDeleted')) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Family::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $familyData = [
            'name' => array_get($data, 'Name'),
            'tenant_id' => auth()->user()->tenant_id
        ];
        
        $family = mapModel(new Family(), $familyData);
        $family->save();
        
        $this->alternativeIdCreate(array_get($family, 'id'), get_class($family), [
            'alt_id' => $altId,
            'label' => array_get($family, 'name'),
            'system_created_by' => 'Salesforce'
        ]);
    }
    
    public function syncContacts($date) 
    {
        $total = $this->getContacts(null, 0, $date);
        $totalContacts = array_get($total, 'totalSize');
        
        if ($totalContacts === 0) {
            return 'No new contacts';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalContacts; $i+=$this->take) {
            $contacts = $this->getContacts($start, $this->take, $date);
            
            if (array_get($contacts, 'totalSize') > 0) {
                foreach (array_get($contacts, 'records') as $contact) {
                    $this->importContact($contact);
                    $start = array_get($contact, 'Id');
                }
            }
        }
    }
    
    public function importContact($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // do not get deleted
        if (array_get($data, 'IsDeleted')) {
            return false;
        }
        
        if (array_get($data, 'LastName') === 'Kent Co. Business Owner') {
            return false;
        }
        
        $contactData = [
            'type' => 'person',
            'last_name' => nullIfEmpty(array_get($data, 'LastName')),
            'first_name' => nullIfEmpty(array_get($data, 'FirstName')),
            'salutation' => nullIfEmpty(array_get($data, 'Salutation')),
            'cell_phone' => nullIfEmpty($this->getCellPhone($data)),
            'home_phone' => nullIfEmpty(array_get($data, 'HomePhone')),
            'other_phone' => nullIfEmpty($this->getOtherPhone($data)),
            'email_1' => array_get($data, 'Email'),
            'position' => nullIfEmpty(array_get($data, 'Occupation__c') ? array_get($data, 'Occupation__c') : array_get($data, 'Title')),
            'source' => nullIfEmpty(array_get($data, 'LeadSource')),
            'dob' => array_get($data, 'Birthdate'),
            'background_info' => array_get($data, 'Description'),
            'unsubscribed' => array_get($data, 'HasOptedOutOfEmail') || array_get($data, 'npsp__Do_Not_Contact__c') ? date('Y-m-d H:i:s') : null,
            'email_2' => $this->getEmail2($data),
            'company' => nullIfEmpty(array_get($data, 'Employer__c'))
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
        
        // Contact does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            // Check first if we already have a contact with same email, if yes than use that
            if (!empty(array_get($contactData, 'email_1'))) {
                $contact = Contact::where('email_1', array_get($contactData, 'email_1'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from Salesforce
                    // Doing this because email in Salesforce might not be unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'Salesforce')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_1'] = null; // Unset email 1
                    }
                }
            }
            
            if (empty($contact)) {
                $contact = new Contact();
            
                $contact = mapModel($contact, $contactData);

                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }

                $contact->refresh();

                $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), [
                    'alt_id' => $altId,
                    'label' => array_get($contact, 'full_name'),
                    'system_created_by' => 'Salesforce'
                ]);
            }
        } else {
            $contact = Contact::find(array_get($altIdObject, 'relation_id'));
            
            if ($contact) {
                mapModel($contact, $contactData);
                $contact->update();
            }
        }
        
        if ($contact) {
            $this->importContactAddresses($contact, $data);
            
            $this->importCustomFields($contact, $data);
            
            $this->importContactFamily($contact, array_get($data, 'AccountId'), array_get($data, 'npsp__Primary_Contact__c'));
        }
    }
    
    private function getCellPhone($data)
    {
        if (array_get($data, 'Phone')) {
            return array_get($data, 'Phone');
        } elseif (array_get($data, 'MobilePhone')) {
            return array_get($data, 'MobilePhone');
        } elseif (array_get($data, 'HomePhone')) {
            return array_get($data, 'HomePhone');
        } elseif (array_get($data, 'OtherPhone')) {
            return array_get($data, 'OtherPhone');
        }
    }
    
    private function getOtherPhone($data)
    {
        $phone = null;
        
        if (array_get($data, 'Phone') && array_get($data, 'MobilePhone') && array_get($data, 'MobilePhone') != array_get($data, 'Phone')) {
            $phone = array_get($data, 'MobilePhone');
        }
        
        if (array_get($data, 'OtherPhone')) {
            $phone.= ','.array_get($data, 'OtherPhone');
        }
        
        if ($phone) {
            $phone = trim($phone, ',');
        }
         
        return $phone;
    }
    
    private function getEmail2($data)
    {
        $email = null;
        
        if (array_get($data, 'npe01__AlternateEmail__c') && array_get($data, 'npe01__AlternateEmail__c') !== array_get($data, 'Email')) {
            $email.= array_get($data, 'npe01__AlternateEmail__c');
        }
        
        if (array_get($data, 'npe01__HomeEmail__c') && array_get($data, 'npe01__HomeEmail__c') !== array_get($data, 'Email')) {
            $email.= ','.array_get($data, 'npe01__HomeEmail__c');
        }
        
        if ($email) {
            $email = trim($email, ',');
        }
        
        return $email;
    }
    
    private function importContactAddresses(Contact $contact, $data)
    {
        if (array_get($data, 'MailingStreet') || array_get($data, 'MailingCity') || array_get($data, 'MailingState') || array_get($data, 'MailingPostalCode')) {
            $contact->addresses()->delete();
            
            $addressData = [
                'mailing_address_1' => nullIfEmpty(array_get($data, 'MailingStreet')),
                'city' => nullIfEmpty(array_get($data, 'MailingCity')),
                'region' => nullIfEmpty(array_get($data, 'MailingState')),
                'postal_code' => nullIfEmpty(array_get($data, 'MailingPostalCode')),
                'country' => nullIfEmpty(array_get($data, 'MailingCountry')),
                'is_residence' => array_get($data, 'npe01__Primary_Address_Type__c') === 'Home' ? 1 : 0,
                'is_mailing' => 1,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];
            
            $address = mapModel(new Address(), $addressData);
            $address->save();
        }
        
        if (array_get($data, 'OtherStreet') || array_get($data, 'OtherCity') || array_get($data, 'OtherState') || array_get($data, 'OtherPostalCode')) {
            $addressData = [
                'mailing_address_1' => nullIfEmpty(array_get($data, 'OtherStreet')),
                'city' => nullIfEmpty(array_get($data, 'OtherCity')),
                'region' => nullIfEmpty(array_get($data, 'OtherState')),
                'postal_code' => nullIfEmpty(array_get($data, 'OtherPostalCode')),
                'country' => nullIfEmpty(array_get($data, 'OtherCountry')),
                'is_residence' => array_get($data, 'npe01__Secondary_Address_Type__c') === 'Home' ? 1 : 0,
                'is_mailing' => 0,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];
            
            $address = mapModel(new Address(), $addressData);
            $address->save();
        }
    }
    
    public function importCustomFields(Contact $contact, $data)
    {
        $field = CustomField::where('code', 'volunteer_availability__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(str_replace(';', ',', array_get($data, 'GW_Volunteers__Volunteer_Availability__c')), $contact, $field);
        }
        
        $field = CustomField::where('code', 'volunteer_last_web_signup_date__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'GW_Volunteers__Volunteer_Last_Web_Signup_Date__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'volunteer_organization__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'GW_Volunteers__Volunteer_Organization__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'volunteer_skills__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(str_replace(';', ',', array_get($data, 'GW_Volunteers__Volunteer_Skills__c')), $contact, $field);
        }
        
        $field = CustomField::where('code', 'first_volunteer_date__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'GW_Volunteers__First_Volunteer_Date__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'last_volunteer_date__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'GW_Volunteers__Last_Volunteer_Date__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'volunteer_hours__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'GW_Volunteers__Volunteer_Hours__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'carpenter_club__c')->first();
        if ($field) {
            $value = array_get($data, 'Carpenter_Club__c') ? 'Yes' : '';
            CustomFieldValue::createOrUpdate($value, $contact, $field);
        }
        
        $field = CustomField::where('code', 'volunteer_category__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(str_replace(';', ',', array_get($data, 'Volunteer_Category__c')), $contact, $field);
        }
        
        $field = CustomField::where('code', 'habitat_hero__c')->first();
        if ($field) {
            $value = array_get($data, 'Habitat_Hero__c') ? 'Yes' : '';
            CustomFieldValue::createOrUpdate($value, $contact, $field);
        }
        
        $field = CustomField::where('code', 'other_program_name__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Other_Program_Name__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'other_reason_for_volunteering__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Other_Reason_for_Volunteering__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'reason_for_volunteering__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Reason_for_Volunteering__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'waiver_date__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Waiver_Date__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'restore_volunteer__c')->first();
        if ($field) {
            $value = array_get($data, 'ReStore_Volunteer__c') ? 'Yes' : '';
            CustomFieldValue::createOrUpdate($value, $contact, $field);
        }
        
        $field = CustomField::where('code', 'group_name__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Group_Name__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'place_of_worship__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Place_of_Worship__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'other_community_groups__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Other_Community_Groups__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'how_did_you_hear_about_us__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'How_Did_You_Hear_About_Us__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_first_name')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_First_Name__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_last_name__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Last_Name__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_street_address__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Street_Address__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_city__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_City__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_state__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_State__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_zip_code__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Zip_Code__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_home_phone__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Home_Phone__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_cell_phone__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Cell_Phone__c'), $contact, $field);
        }
        
        $field = CustomField::where('code', 'emergency_contact_relationship__c')->first();
        if ($field) {
            CustomFieldValue::createOrUpdate(array_get($data, 'Emergency_Contact_Relationship__c'), $contact, $field);
        }
    }
    
    public function importContactFamily(Contact $contact, $altId, $isPrimaryContact)
    {
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Family::class);
        
        if (empty($altIdObject)) {
            return false;
        }
        
        array_set($contact, 'family_id', array_get($altIdObject, 'relation_id'));
        array_set($contact, 'family_position', ($isPrimaryContact ? 'Primary Contact' : 'Other'));
        $contact->update();
    } 
    
    public function syncOrganizations($date)
    {
        $total = $this->getAccounts(null, 0, $date);
        $totalOrganizations = array_get($total, 'totalSize');
        
        if ($totalOrganizations === 0) {
            return 'No new organizations';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalOrganizations; $i+=$this->take) {
            $organizations = $this->getAccounts($start, $this->take, $date);
            
            if (array_get($organizations, 'totalSize') > 0) {
                foreach (array_get($organizations, 'records') as $organization) {
                    $this->importOrganization($organization);
                    $start = array_get($organization, 'Id');
                }
            }
        }
    }
    
    public function importOrganization($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // do not get deleted
        if (array_get($data, 'IsDeleted')) {
            return false;
        }
        
        $contactData = [
            'type' => 'organization',
            'company' => nullIfEmpty(array_get($data, 'Name')),
            'cell_phone' => nullIfEmpty(array_get($data, 'Phone')),
            'website' => nullIfEmpty(array_get($data, 'Website')),
            'background_info' => array_get($data, 'Description')
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
        
        // Contact does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            // Check first if we already have a contact with same company, if yes than use that
            if (!empty(array_get($contactData, 'company'))) {
                $contact = Contact::where('company', array_get($contactData, 'company'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from Salesforce
                    // Doing this because email in Salesforce might not be unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'Salesforce')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_1'] = null; // Unset email 1
                    }
                }
            }
            
            if (empty($contact)) {
                $contact = new Contact();
            
                $contact = mapModel($contact, $contactData);

                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }

                $contact->refresh();

                $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), [
                    'alt_id' => $altId,
                    'label' => array_get($contact, 'company'),
                    'system_created_by' => 'Salesforce'
                ]);
            }
        } else {
            $contact = Contact::find(array_get($altIdObject, 'relation_id'));
            
            if ($contact) {
                mapModel($contact, $contactData);
                $contact->update();
            }
        }
        
        if ($contact) {
            $this->importOrganizationAddresses($contact, $data);
        }
        
        return $contact;
    }
    
    private function importOrganizationAddresses(Contact $contact, $data)
    {
        if (array_get($data, 'BillingStreet') || array_get($data, 'BillingCity') || array_get($data, 'BillingState') || array_get($data, 'BillingPostalCode')) {
            $contact->addresses()->delete();
            
            $addressData = [
                'mailing_address_1' => nullIfEmpty(array_get($data, 'BillingStreet')),
                'city' => nullIfEmpty(array_get($data, 'BillingCity')),
                'region' => nullIfEmpty(array_get($data, 'BillingState')),
                'postal_code' => nullIfEmpty(array_get($data, 'BillingPostalCode')),
                'country' => nullIfEmpty(array_get($data, 'BillingCountry')),
                'is_residence' => 0,
                'is_mailing' => 0,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];
            
            $address = mapModel(new Address(), $addressData);
            $address->save();
        }
        
        if (array_get($data, 'ShippingStreet') || array_get($data, 'ShippingCity') || array_get($data, 'ShippingState') || array_get($data, 'ShippingPostalCode')) {
            $addressData = [
                'mailing_address_1' => nullIfEmpty(array_get($data, 'ShippingStreet')),
                'city' => nullIfEmpty(array_get($data, 'ShippingCity')),
                'region' => nullIfEmpty(array_get($data, 'ShippingState')),
                'postal_code' => nullIfEmpty(array_get($data, 'ShippingPostalCode')),
                'country' => nullIfEmpty(array_get($data, 'ShippingCountry')),
                'is_residence' => 0,
                'is_mailing' => 1,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];
            
            $address = mapModel(new Address(), $addressData);
            $address->save();
        }
    }
    
    public function syncRelationships($date)
    {
        $total = $this->getAffiliations(null, 0, $date);
        $totalRelationships = array_get($total, 'totalSize');
        
        if ($totalRelationships === 0) {
            return 'No new relationship';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalRelationships; $i+=$this->take) {
            $relationships = $this->getAffiliations($start, $this->take, $date);
            
            if (array_get($relationships, 'totalSize') > 0) {
                foreach (array_get($relationships, 'records') as $relationship) {
                    $this->importRelationship($relationship);
                    $start = array_get($relationship, 'Id');
                }
            }
        }
    }
    
    public function importRelationship($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // do not get deleted
        if (array_get($data, 'IsDeleted')) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, 'contact_relatives');
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $contactAltIdObject = AltId::where('alt_id', array_get($data, 'npe5__Contact__c'))->where('relation_type', Contact::class)->where('system_created_by', 'Salesforce')->first();
        
        if (empty($contactAltIdObject)) {
            return false;
        }
        
        $organizationAltIdObject = AltId::where('alt_id', array_get($data, 'npe5__Organization__c'))->where('relation_type', Contact::class)->where('system_created_by', 'Salesforce')->first();
        
        if (empty($organizationAltIdObject)) {
            return false;
        }
        
        $contact = Contact::find(array_get($contactAltIdObject, 'relation_id'));
        
        if (empty($contact)) {
            return false;
        }
        
        $organization = Contact::find(array_get($organizationAltIdObject, 'relation_id'));
        
        if (empty($organization)) {
            return false;
        }
        
        // check if we already have this relation
        foreach ($contact->relatives as $relative) {
            if (array_get($relative, 'id') == array_get($organizationAltIdObject, 'relation_id')) {
                return false;
            }
        }

        foreach ($contact->relativesUp as $relative) {
            if (array_get($relative, 'id') == array_get($organizationAltIdObject, 'relation_id')) {
                return false;
            }
        }

        $sync = [
            array_get($organizationAltIdObject, 'relation_id') => [
                'contact_relationship' => array_get($data, 'npe5__Role__c') ? array_get($data, 'npe5__Role__c') : 'Employee',
                'relative_relationship' => 'Employer'
            ]
        ];
        $contact->relatives()->sync($sync, false);
        
        $this->alternativeIdCreate(array_get($contact, 'id'), 'contact_relatives', [
            'alt_id' => $altId,
            'label' => array_get($data, 'Name'),
            'system_created_by' => 'Salesforce'
        ]);
        
        if (empty(array_get($organization, 'first_name')) && empty(array_get($organization, 'last_name'))) {
            $organization->first_name = array_get($contact, 'first_name');
            $organization->last_name = array_get($contact, 'last_name');
            $organization->update();
        }
    }
    
    public function syncCampaigns($date)
    {
        $total = $this->getCampaigns(null, 0, $date);
        $totalCampaigns = array_get($total, 'totalSize');
        
        if ($totalCampaigns === 0) {
            return 'No new households';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalCampaigns; $i+=$this->take) {
            $campaigns = $this->getCampaigns($start, $this->take, $date);
            
            if (array_get($campaigns, 'totalSize') > 0) {
                foreach (array_get($campaigns, 'records') as $campaign) {
                    $this->importCampaign($campaign);
                    $start = array_get($campaign, 'Id');
                }
            }
        }
    }
    
    public function importCampaign($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $purpose = Purpose::where('sub_type', 'organizations')->first();
        
        $campaignData = [
            'purpose_id' => array_get($purpose, 'id'),
            'name' => nullIfEmpty(array_get($data, 'Name')),
            'receive_donations' => 0,
            'page_type' => 'givingpage',
            'sub_type' => 'givingpages'
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Campaign::class);
        
        // Campaign does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            $campaign = new Campaign();
            mapModel($campaign, $campaignData);

            if (!auth()->user()->tenant->campaigns()->save($campaign)) {
                abort(500);
            }
            
            $campaign->refresh();
            
            $this->alternativeIdCreate(array_get($campaign, 'id'), get_class($campaign), [
                'alt_id' => $altId,
                'label' => array_get($campaign, 'name'),
                'system_created_by' => 'Salesforce'
            ]);
        }
    }
    
    public function syncTransactions($date)
    {
        $total = $this->getOpportunities(null, 0, $date);
        $totalTransactions = array_get($total, 'totalSize');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        $start = null;
        
        for ($i = 0; $i < $totalTransactions; $i+=$this->take) {
            $transactions = $this->getOpportunities($start, $this->take, $date);
            
            if (array_get($transactions, 'totalSize') > 0) {
                foreach (array_get($transactions, 'records') as $transaction) {
                    $this->importTransaction($transaction);
                    $start = array_get($transaction, 'Id');
                }
            }
        }
    }
    
    public function importTransaction($data)
    {
        $altId = array_get($data, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // do not get deleted
        if (array_get($data, 'IsDeleted')) {
            return false;
        }
        
        if (!array_get($data, 'Amount')) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $transactionTime = nullIfEmpty(array_get($data, 'CloseDate').' 12:00:00');
        if (!is_null($transactionTime)) {
            // TODO - Find a better way to handle localization according to tenant local time
            $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
        }
        
        $transactionTemplateData = [
            'completion_datetime' => $transactionTime,
            'amount' => array_get($data, 'Amount'),
            'is_recurring' => 0,
            'is_pledge' => 0,
            'successes' => 1,
            'acknowledged' => array_get($data, 'npsp__Acknowledgment_Status__c') === 'Acknowledged' ? 1 : 0
        ];
        
        $transactionData = [
            'transaction_initiated_at' => $transactionTime,
            'transaction_last_updated_at' => $transactionTime,
            'channel' => 'Unknown',
            'system_created_by' => 'Salesforce',
            'status' => 'complete',
            'transaction_path' => 'Ssalesforce',
            'anonymous_amount' => 'protected',
            'anonymous_identity' => 'protected',
            'type' => 'donation',
            'acknowledged' => array_get($data, 'npsp__Acknowledgment_Status__c') === 'Acknowledged' ? 1 : 0,
            'acknowledged_at' => nullIfEmpty(array_get($data, 'npsp__Acknowledgment_Date__c')),
            'comment' => array_get($data, 'Description')
        ];
        
        if (array_get($data, 'ContactId')) {
            $contactAltid = array_get($data, 'ContactId');
        } else {
            $contactAltid = array_get($data, 'AccountId');
        }        
        
        if (!empty($contactAltid)) {
            $contactAltidObject = $this->alternativeIdRetrieve($contactAltid, Contact::class);
            if (!empty($contactAltidObject)) {
                $transactionTemplateData['contact_id'] = array_get($contactAltidObject, 'relation_id');
                $transactionData['contact_id'] = array_get($contactAltidObject, 'relation_id');
            }
        }
        
        if (!array_get($transactionData, 'contact_id')) {
            $contactId = $this->addContactForTransaction($data);
            $transactionTemplateData['contact_id'] = $contactId;
            $transactionData['contact_id'] = $contactId;
        } 
        
        if (!array_get($transactionData, 'contact_id')) {
            return false;
        }
        
        $purposeId = $this->getPurposeId($data);
        $campaignId = $this->getCampaignId($data);

        $transactionSplitData = [
            'template' => [
                'campaign_id' => $campaignId,
                'purpose_id' => $purposeId,
                'type' => 'donation',
                'amount' => nullIfEmpty(array_get($data, 'Amount')),
                'splitAltId' => array_get($data, 'Id'),
            ],
            'transaction' => [
                'campaign_id' => $campaignId,
                'purpose_id' => $purposeId,
                'amount' => nullIfEmpty(array_get($data, 'Amount')),
                'type' => 'donation',
                'splitAltId' => array_get($data, 'Id')
            ]
        ];
        
        // Transaction does not exist so we make a new one, else do nothing
        if (empty($altIdObject)) {
            if ($transactionData['contact_id']) {
                $transactionData['payment_option_id'] = $this->getPaymentOptionId($transactionData['contact_id']);
            }
            
            $transactionTemplate = $this->storeTransactionTemplate($transactionTemplateData, $altId);
            
            $transactionData['transaction_template_id'] = array_get($transactionTemplate, 'id');
            
            $transaction = $this->storeTransaction($transactionData, $altId);
            
            $transactionSplitData['template']['transaction_template_id'] = array_get($transactionTemplate, 'id');
            $splitTemplate = $this->storeTransactionTemplateSplit($transactionSplitData['template']);

            $transactionSplitData['transaction']['transaction_id'] = array_get($transaction, 'id');
            $transactionSplitData['transaction']['transaction_template_split_id'] = array_get($splitTemplate, 'id');
            $this->storeTransactionSplit($transactionSplitData['transaction']);
        }
    }
    
    public function addContactForTransaction($data)
    {
        if (array_get($data, 'AccountId')) {
            $account = $this->getAccountById(array_get($data, 'AccountId'));
            
            if ($account) {
                $contact = $this->importOrganization($account);
                return array_get($contact, 'id');
            }
        } 
    }
    
    public function getPurposeId($split)
    {
        $parentPurpose = Purpose::where('sub_type', 'organizations')->first();
        
        if (empty(array_get($split, 'Name'))) {
            return array_get($parentPurpose, 'id');
        }
        
        $purpose = Purpose::where('name', array_get($split, 'Name'))->first();
        
        if (empty($purpose)) {
            $purpose = new Purpose();
            $purpose->tenant_id = auth()->user()->tenant->id;
            $purpose->parent_purposes_id = array_get($parentPurpose, 'id');
            $purpose->name = array_get($split, 'Name');
            $purpose->receive_donations = 0;
            $purpose->page_type = 'project';
            $purpose->sub_type = 'projects';
            $purpose->type = 'Purpose';
            $purpose->is_active = 0;
            $purpose->save();
        }
        
        return array_get($purpose, 'id');
    }
    
    public function getCampaignId($split)
    {
        if (empty(array_get($split, 'CampaignId'))) {
            return 1;
        }
        
        $campaingId = array_get($split, 'CampaignId');
        
        if (empty($campaingId)) {
            return 1;
        }
        
        $campaignAltObject = $this->alternativeIdRetrieve($campaingId, Campaign::class);
        
        if (empty($campaignAltObject)) {
            return 1;
        } else {
            return array_get($campaignAltObject, 'relation_id');
        }
    }
    
    public function getPaymentOptionId($contactId)
    {
        if (!$contactId) {
            return null;
        }
        
        $paymentOptionData = ['contact_id' => $contactId];
        $paymentOptionData['category'] = 'unknown';
        $paymentOptionData['last_four'] = null;

        $paymentOption = PaymentOption::where([
            ['contact_id', '=', $contactId],
            ['category', '=', $paymentOptionData['category']],
        ])->where(function ($q) use ($paymentOptionData) {
            $q->where('last_four', $paymentOptionData['last_four'])->orWhereNull('last_four');
        })->first();

        if (is_null($paymentOption)) {
            $paymentOption = mapModel(new PaymentOption(), $paymentOptionData);
            auth()->user()->tenant->paymentOptions()->save($paymentOption);
        }
        
        return array_get($paymentOption, 'id');
    }
    
    public function storeTransactionTemplate($data, $altId)
    {
        $transactionTemplate = new TransactionTemplate();
        mapModel($transactionTemplate, $data);

        if (!auth()->user()->tenant->transactionTemplates()->save($transactionTemplate)) {
            abort(500);
        }

        $transactionTemplate->refresh();

        $this->alternativeIdCreate(array_get($transactionTemplate, 'id'), get_class($transactionTemplate), [
            'alt_id' => $altId,
            'label' => 'Transaction Template',
            'system_created_by' => 'Salesforce'
        ]);
        
        return $transactionTemplate;
    }
    
    public function storeTransaction($data, $altId)
    {
        $transaction = new Transaction();
        mapModel($transaction, $data);

        if (!auth()->user()->tenant->transactions()->save($transaction)) {
            abort(500);
        }

        $transaction->refresh();

        $this->alternativeIdCreate(array_get($transaction, 'id'), get_class($transaction), [
            'alt_id' => $altId,
            'label' => 'Transaction',
            'system_created_by' => 'Salesforce'
        ]);
        
        return $transaction;
    }
    
    public function storeTransactionTemplateSplit($data)
    {
        $split = new TransactionTemplateSplit();
        mapModel($split, $data);

        if (!auth()->user()->tenant->transactionTemplateSplits()->save($split)) {
            abort(500);
        }

        $split->refresh();

        $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), [
            'alt_id' => array_get($data, 'splitAltId'),
            'label' => 'Transaction Template Split',
            'system_created_by' => 'Salesforce'
        ]);
        
        return $split;
    }
    
    public function storeTransactionSplit($data)
    {
        $split = new TransactionSplit();
        mapModel($split, $data);

        if (!auth()->user()->tenant->transactionSplits()->save($split)) {
            abort(500);
        }

        $split->refresh();

        $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), [
            'alt_id' => array_get($data, 'splitAltId'),
            'label' => 'Transaction Split',
            'system_created_by' => 'Salesforce'
        ]);
        
        return $split;
    }
}
