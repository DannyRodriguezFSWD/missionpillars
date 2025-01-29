<?php

namespace App\Classes\CCB;

use App\Models\Address;
use App\Models\AltId;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Purpose;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Models\User;
use App\Traits\AlternativeIdTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Ramsey\Uuid\Uuid;

class CCB extends CCBAPI
{
    use AlternativeIdTrait;
    
    private $page = 1;
    private $perPage = 1000;
    
    public function sync($date)
    {
        abort(404);
        
        ini_set('max_execution_time', 300);
        
//        $this->syncContacts($date);
//        $this->syncGroups($date);
//        $this->syncPurposes();
//        $this->syncTransactions($date);
    }
    
    public function syncContacts($date) 
    {
        $ccbContacts = $this->individualProfiles($date, $this->page, $this->perPage);
        
        $totalContacts = (int)$ccbContacts->attributes()->count;
        
        if ($totalContacts === 0) {
            return 'No new contacts';
        }
        
        foreach ($ccbContacts->individual as $individual) {
            $this->importContact($individual);
        }
    }
    
    public function importContact(\SimpleXMLElement $ccbData)
    {
        $altId = (string)$ccbData->attributes()->id;
        
        if (empty($altId)) {
            return false;
        }
        
        $contactData = [
            'first_name' => nullIfEmpty((string)$ccbData->first_name),
            'last_name' => nullIfEmpty((string)$ccbData->last_name),
            'middle_name' => nullIfEmpty((string)$ccbData->middle_name),
            'salutation' => nullIfEmpty((string)$ccbData->salutation),
            'email_1' => nullIfEmpty((string)$ccbData->email),
            'allergies' => nullIfEmpty((string)$ccbData->allergies),
            'confirmed_no_allergies' => (string)$ccbData->confirmed_no_allergies === 'true' ? 1 : 0,
            'marital_status' => nullIfEmpty((string)$ccbData->marital_status),
            'dob' => nullIfEmpty((string)$ccbData->birthday),
            'membership_type' => nullIfEmpty((string)$ccbData->membership_type),
            'membership_start_date' => nullIfEmpty((string)$ccbData->membership_date),
            'membership_end_date' => nullIfEmpty((string)$ccbData->membership_end),
            'unsubscribed' => (string)$ccbData->receive_email_from_church === 'false' ? date('Y-m-d H:i:s') : null,
            'active' => (string)$ccbData->active === 'true' ? 1 : 0,
            'anniversary' => nullIfEmpty((string)$ccbData->anniversary),
            'baptized' => (string)$ccbData->baptized === 'true' ? 1 : 0,
            'deceased' => nullIfEmpty((string)$ccbData->deceased),
            'email_2' => self::getCustomField($ccbData, 'text', array_get($this->customFields, 'email_2')),
            'background_check' => self::getCustomField($ccbData, 'date', array_get($this->customFields, 'background_check')),
            'cell_phone' => self::getPhone($ccbData, 'mobile'),
            'home_phone' => self::getPhone($ccbData, 'home'),
            'work_phone' => self::getPhone($ccbData, 'work'),
            'gender' => self::getGender($ccbData),
            'limited_access_user' => (string)$ccbData->limited_access_user === 'true' ? 1 : 0
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
        
        // Contact does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            // Check first if we already have a contact with same email, if yes than use that
            if (!empty(array_get($contactData, 'email_1'))) {
                $contact = Contact::where('email_1', array_get($contactData, 'email_1'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from CCB
                    // Doing this because email in CCB are not unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'CCB')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_1'] = null; // Unset email 1
                    }
                }
            } elseif (!empty(array_get($contactData, 'email_2'))) {
                $contact = Contact::where('email_2', array_get($contactData, 'email_2'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from CCB
                    // Doing this because email in CCB are not unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'CCB')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_2'] = null; // Unset email 2
                    }
                }
            }
            
            if (empty($contact)) {
                $contact = new Contact();
            }
            
            $contact = mapModel($contact, $contactData);

            if (!auth()->user()->tenant->contacts()->save($contact)) {
                abort(500);
            }
            
            $contact->refresh();
            
            $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), [
                'alt_id' => $altId,
                'label' => array_get($contact, 'full_name'),
                'system_created_by' => 'CCB'
            ]);
        } else {
            $contact = Contact::findOrFail(array_get($altIdObject, 'relation_id'));
            mapModel($contact, $contactData);
            $contact->update();
        }
        
        $this->importContactAddress($contact, $ccbData->addresses->address);
        
        $this->importProfileImage($contact, (string)$ccbData->image);
        
        $this->importRelatives($contact, (string)$ccbData->family_position,  $ccbData->family_members);
    }
    
    public function createUserForContact($contactData, $limitedAccess = false)
    {
        $user = new User();
        array_set($user, 'name', array_get($contactData, 'first_name'));
        array_set($user, 'last_name', array_get($contactData, 'last_name'));
        array_set($user, 'email', array_get($contactData, 'email_1'));
        array_set($user, 'password', bcrypt(str_random()));
        auth()->user()->tenant->users()->save($user);
        
        $roleName = $limitedAccess ? 'organization-contact' : 'organization-owner';
        $role = Role::where('name', $roleName)->first();
        $user->attachRole($role);
        
        return $user;
    }
    
    public function importContactAddress(Contact $contact, $addresses)
    {
        $contact->addresses()->delete();
        
        foreach ($addresses as $ccbAddress) {
            if (!empty((string)$ccbAddress->street_address)) {
                $type = (string)$ccbAddress->attributes()->type;
                
                $addressData = [
                    'mailing_address_1' => nullIfEmpty((string)$ccbAddress->street_address),
                    'city' => nullIfEmpty((string)$ccbAddress->city),
                    'region' => nullIfEmpty((string)$ccbAddress->state),
                    'postal_code' => nullIfEmpty((string)$ccbAddress->zip),
                    'country' => nullIfEmpty((string)$ccbAddress->country->attributes()->code),
                    'is_residence' => $type === 'home' ? 1 : 0,
                    'is_mailing' => $type === 'mailing' ? 1 : 0,
                    'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                    'relation_id' => array_get($contact, 'id'),
                    'relation_type' => Contact::class
                ];
                
                $address = mapModel(new Address(), $addressData);
                $address->save();
            }
        }
    }
    
    public function importProfileImage(Contact $contact, string $url)
    {
        if (!empty($url) && strpos($url, 'profile-default.gif') === false) {
            if ($image = file_get_contents($url)) {
                $imageResize = Image::make($image)->resize(400, 400);
                $allowedMimeTypes = ['image/bmp', 'image/gif', 'image/jpeg', 'image/png'];
                $mime = $imageResize->mime();
                $mimeEx = explode('/', $mime);
                $ext = $mimeEx[1];
                $fileHash = Str::random(40);
                $filename = $fileHash.'.'.$ext;

                if (in_array($mime, $allowedMimeTypes)) {
                    if (env('AWS_ENABLED')) {
                        if (!empty($contact->profile_image)) {
                            Storage::disk('s3')->delete($contact->profile_image);
                        }
                        
                        Storage::disk('s3')->put('profile_images/'.$filename, $imageResize->stream(), 'public');
                        array_set($contact, 'profile_image', 'profile_images/'.$filename);
                    } else {
                        if (!empty($contact->profile_image)) {
                            checkAndDeleteFile(storage_path('app/public/contacts/' . $contact->profile_image));
                        }
                        
                        $imageResize->save(storage_path('app/public/contacts/'.$filename));
                        array_set($contact, 'profile_image', $filename);
                    }
                }

                $contact->update();
            }
        }
        
        return null;
    }
    
    public function importRelatives(Contact $contact, $familyPosition, \SimpleXMLElement $relatives)
    {
        if (!empty($relatives)) {
            foreach ($relatives->family_member as $relative) {
                $altId = (int)$relative->individual->attributes()->id;        
                $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
                
                if (!empty($altIdObject)) {
                    $sync = [
                        array_get($altIdObject, 'relation_id') => [
                            'contact_relationship' => $familyPosition,
                            'relative_relationship' => (string)$relative->family_position
                        ]
                    ];
                    $contact->relatives()->sync($sync, false);
                }
            }
        }
        
        return null;
    }
    
    public function syncGroups($date)
    {
        $ccbGroups = $this->groupProfiles($date);
        
        $totalGroups = (int)$ccbGroups->attributes()->count;
        
        if ($totalGroups === 0) {
            return 'No new groups';
        }
        
        foreach ($ccbGroups->group as $group) {
            $this->importGroup($group);
        }
    }
    
    public function importGroup(\SimpleXMLElement $ccbData)
    {
        // Do not import groups that do not have any members
        if (empty($ccbData->participants)) {
            return false;
        }
        
        $altId = (string)$ccbData->attributes()->id;
        
        if (empty($altId)) {
            return false;
        }
        
        // Doing this to not get the "All Contacts" group
        // TODO - double check this is the case for all CCB installations
        if ($altId == 1) {
            return false;
        }
        
        $groupData = [
            'name' => nullIfEmpty((string)$ccbData->name),
            'description' => nullIfEmpty((string)$ccbData->description),
            'folder_id' => 10,
            'uuid' => Uuid::uuid1()
        ];
        
        if (!empty((string)$ccbData->main_leader->attributes()->id)) {
            $groupLeaderAltid = $this->alternativeIdRetrieve((string)$ccbData->main_leader->attributes()->id, Contact::class);
            if (!empty($groupLeaderAltid)) {
                $groupData['contact_id'] = array_get($groupLeaderAltid, 'relation_id');
            }
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Group::class);
        
        // Group does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            $group = new Group();
            mapModel($group, $groupData);

            if (!auth()->user()->tenant->groups()->save($group)) {
                abort(500);
            }
            
            $group->refresh();
            
            $this->alternativeIdCreate(array_get($group, 'id'), get_class($group), [
                'alt_id' => $altId,
                'label' => array_get($group, 'name'),
                'system_created_by' => 'CCB'
            ]);
        } else {
            $group = Group::findOrFail(array_get($altIdObject, 'relation_id'));
            mapModel($group, $groupData);
            $group->update();
        }
        
        $this->importGroupAddress($group, $ccbData->addresses->address);
        
        $this->importGroupCoverImage($group, (string)$ccbData->image);
        
        $this->importGroupMembers($group, $ccbData->participants);
    }
        
    public function importGroupAddress(Group $group, $addresses)
    {
        $group->addresses()->delete();
        
        foreach ($addresses as $ccbAddress) {
            if (!empty((string)$ccbAddress->street_address)) {
                $addressData = [
                    'mailing_address_1' => nullIfEmpty((string)$ccbAddress->street_address),
                    'city' => nullIfEmpty((string)$ccbAddress->city),
                    'region' => nullIfEmpty((string)$ccbAddress->state),
                    'postal_code' => nullIfEmpty((string)$ccbAddress->zip),
                    'country' => 'US',
                    'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                    'relation_id' => array_get($group, 'id'),
                    'relation_type' => Group::class
                ];
                
                $address = mapModel(new Address(), $addressData);
                $address->save();
            }
        }
    }
    
    public function importGroupCoverImage(Group $group, string $url)
    {
        if (!empty($url) && strpos($url, 'profile-default.gif') === false) {
            if ($image = file_get_contents($url)) {
                $imageResize = Image::make($image);
                $allowedMimeTypes = ['image/bmp', 'image/gif', 'image/jpeg', 'image/png'];
                $mime = $imageResize->mime();
                $mimeEx = explode('/', $mime);
                $ext = $mimeEx[1];
                $fileHash = Str::random(40);
                $filename = $fileHash.'.'.$ext;

                if (in_array($mime, $allowedMimeTypes)) {
                    if (!empty($group->cover_image)) {
                        unlink(storage_path('app/public/groups/' . $group->cover_image));
                    }

                    $imageResize->save(storage_path('app/public/groups/'.$filename));
                    array_set($group, 'cover_image', $filename);
                }

                $group->update();
            }
        }
        
        return null;
    }
    
    public function importGroupMembers(Group $group, $participants)
    {
        if (empty($participants)) {
            return false;
        }
        
        $allAltIds = [];
        
        foreach ($participants->participant as $participant) {
            $altId = (string)$participant->attributes()->id;
            if (!empty($altId)) {
                $allAltIds[] = $altId;
            }
        }
        
        $contactIds = AltId::whereIn('alt_id', $allAltIds)->where('relation_type', Contact::class)->get()->pluck('relation_id');
        
        if (!empty($contactIds)) {
            $group->contacts()->sync($contactIds);
        }
    }
    
    public function syncPurposes()
    {
        $ccbPurposes = $this->transactionDetailTypeList();
        
        $totalPurposes = (int)$ccbPurposes->attributes()->count;
        
        if ($totalPurposes === 0) {
            return 'No new transactions';
        }
        
        foreach ($ccbPurposes->transaction_detail_type as $purpose) {
            $this->importPurpose($purpose);
        }
    }
    
    public function importPurpose(\SimpleXMLElement $ccbData)
    {
        $altId = (string)$ccbData->attributes()->id;
        
        if (empty($altId)) {
            return false;
        }
        
        $purpose = Purpose::where('accounting_integration_coa', $altId)->first();
        
        // Check if we already have the purpose from C2G
        if (!empty($purpose)) {
            return false;
        }
        
        $parentPurpose = Purpose::where('sub_type', 'organizations')->first();
        
        $purposeData = [
            'parent_purposes_id' => array_get($parentPurpose, 'id'),
            'name' => nullIfEmpty((string)$ccbData->name),
            'receive_donations' => 1,
            'page_type' => 'project',
            'sub_type' => 'projects',
            'tax_deductable' => (string)$ccbData->tax_deductible === 'true' ? 1 : 0,
            'type' => 'Purpose',
            'accounting_integration_coa' => $altId,
        ];
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Purpose::class);
        
        // Purpose does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            $purpose = new Purpose();
            mapModel($purpose, $purposeData);

            if (!auth()->user()->tenant->purposes()->save($purpose)) {
                abort(500);
            }
            
            $purpose->refresh();
            
            $this->alternativeIdCreate(array_get($purpose, 'id'), get_class($purpose), [
                'alt_id' => $altId,
                'label' => array_get($purpose, 'name'),
                'system_created_by' => 'CCB'
            ]);
        } else {
            $purpose = Purpose::findOrFail(array_get($altIdObject, 'relation_id'));
            mapModel($purpose, $purposeData);
            $purpose->update();
        }
    }
    
    public function syncTransactions($date)
    {
        $ccbTransactions = $this->batchProfiles($date);
        
        $totalBatches = (int)$ccbTransactions->attributes()->count;
        
        if ($totalBatches === 0) {
            return 'No new batches';
        }
        
        foreach ($ccbTransactions->batch as $batch) {
            if (!empty($batch->transactions)) {
                foreach ($batch->transactions->transaction as $transaction) {
                    $this->importTransaction($transaction);
                }
            }
        }
    }
    
    public function importTransaction(\SimpleXMLElement $ccbData)
    {
        $altId = (string)$ccbData->attributes()->id;
        
        if (empty($altId)) {
            return false;
        }
        
        // This is their CTG ID
        // TODO - Double check if this is true for future cases
        $merchantTransactionId = nullIfEmpty((string)$ccbData->attributes()->merchant_transaction_id);
        $merchantTransactionAltId = $this->alternativeIdRetrieve($merchantTransactionId, Transaction::class);
        
        // We are not doing anything since the transaction is already there
        if (!empty($merchantTransactionAltId)) {
            return false;
        }
        
        $transactionTime = nullIfEmpty((string)$ccbData->date.' 12:00:00');
        if (!is_null($transactionTime)) {
            // TODO - Find a better way to handle localization according to tenant local time
            $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
        }
        
        $transactionTemplateData = [
            'completion_datetime' => $transactionTime,
            'amount' => 0,
            'is_recurring' => 0,
            'is_pledge' => 0,
            'successes' => 1,
        ];
        
        $transactionData = [
            'transaction_initiated_at' => $transactionTime,
            'transaction_last_updated_at' => $transactionTime,
            'channel' => (string)$ccbData->payment_type === 'Online' ? 'website' : 'unknown',
            'check_number' => nullIfEmpty(onlyNumbers((string)$ccbData->check_number)),
            'tax_deductible' => (string)$ccbData->transaction_details->transaction_detail[0]->tax_deductible === "true" ? 1 : 0,
            'system_created_by' => 'CCB',
            'status' => 'complete',
            'transaction_path' => 'ccb',
            'anonymous_amount' => 'protected',
            'anonymous_identity' => 'protected',
            'type' => 'donation',
        ];
        
        $contactAltid = (string)$ccbData->individual->attributes()->id;
        if (!empty($contactAltid)) {
            $contactAltidObject = $this->alternativeIdRetrieve($contactAltid, Contact::class);
            if (!empty($contactAltidObject)) {
                $transactionTemplateData['contact_id'] = array_get($contactAltidObject, 'relation_id');
                $transactionData['contact_id'] = array_get($contactAltidObject, 'relation_id');
            }
        }
        
        $transactionSplitData = [];
        
        foreach ($ccbData->transaction_details->transaction_detail as $split) {
            $purposeId = $this->getPurposeId($split);
            
            $transactionSplitData[] = [
                'template' => [
                    'campaign_id' => 1, // TODO - Make this dynamic in case they use giving pages
                    'purpose_id' => $purposeId,
                    'tax_deductible' => (string)$split->tax_deductible === 'true' ? 1 : 0,
                    'type' => 'donation',
                    'amount' => nullIfEmpty((string)$split->amount),
                    'splitAltId' => (string)$split->attributes()->id,
                ],
                'transaction' => [
                    'campaign_id' => 1, // TODO - Make this dynamic in case they use giving pages
                    'purpose_id' => $purposeId,
                    'amount' => nullIfEmpty((string)$split->amount),
                    'type' => 'donation',
                    'tax_deductible' => (string)$split->tax_deductible === 'true' ? 1 : 0,
                    'splitAltId' => (string)$split->attributes()->id,
                ]
            ];
            
            $transactionTemplateData['amount']+= nullIfEmpty((string)$split->amount);
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);
        
        // Transaction does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            $transactionTemplate = $this->storeTransactionTemplate($transactionTemplateData, $altId);
            
            $transactionData['transaction_template_id'] = array_get($transactionTemplate, 'id');
            
            $transaction = $this->storeTransaction($transactionData, $altId);
            
            foreach ($transactionSplitData as $split) {
                $split['template']['transaction_template_id'] = array_get($transactionTemplate, 'id');
                $splitTemplate = $this->storeTransactionTemplateSplit($split['template']);
            
                $split['transaction']['transaction_id'] = array_get($transaction, 'id');
                $split['transaction']['transaction_template_split_id'] = array_get($splitTemplate, 'id');
                $this->storeTransactionSplit($split['transaction']);
            }
        }
    }
    
    public function getPurposeId(\SimpleXMLElement $split)
    {
        $parentPurpose = Purpose::where('sub_type', 'organizations')->first();
        
        if (empty($split->coa)) {
            return array_get($parentPurpose, 'id');
        }
        
        $accountingIntegrationCoa = (string)$split->coa->attributes()->id;
        
        if (empty($accountingIntegrationCoa)) {
            return array_get($parentPurpose, 'id');;
        }
        
        $purpose = Purpose::where('accounting_integration_coa', $accountingIntegrationCoa)->first();
        
        if (empty($purpose)) {
            return array_get($parentPurpose, 'id');;
        } else {
            return array_get($purpose, 'id');
        }
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
            'system_created_by' => 'CCB'
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
            'system_created_by' => 'CCB'
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
            'system_created_by' => 'CCB'
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
            'system_created_by' => 'CCB'
        ]);
        
        return $split;
    }
    
    public static function getPhone(\SimpleXMLElement $xml, $type)
    {
        if (!empty($xml->phones)) {
            foreach ($xml->phones->phone as $ccbPhone) {
                if ((string)$ccbPhone->attributes()->type === $type) {
                    return nullIfEmpty((string)$ccbPhone);
                }
            }
        } else {
            return null;
        }
    }
    
    public static function getGender(\SimpleXMLElement $xml)
    {
        if (!empty($xml->gender)) {
            if ((string)$xml->gender === 'F') {
                return 'Female';
            } elseif ((string)$xml->gender === 'M') {
                return 'Male';
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    public static function getCustomField(\SimpleXMLElement $xml, $fieldType, $label)
    {
        if (!empty($xml->{'user_defined_'.$fieldType.'_fields'})) {
            foreach ($xml->{'user_defined_'.$fieldType.'_fields'} as $field) {
                if ((string)$field->{'user_defined_'.$fieldType.'_field'}->label === $label) {
                    return nullIfEmpty((string)$field->{'user_defined_'.$fieldType.'_field'}->{$fieldType});
                }
            }
        } else {
            return null;
        }
    }
}
