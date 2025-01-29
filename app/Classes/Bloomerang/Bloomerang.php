<?php

namespace App\Classes\Bloomerang;

use App\Models\Address;
use App\Models\AltId;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Family;
use App\Models\Folder;
use App\Models\Note;
use App\Models\PaymentOption;
use App\Models\Purpose;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Traits\AlternativeIdTrait;
use App\Traits\DocumentsTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Bloomerang extends BloomerangAPI
{
    use AlternativeIdTrait, DocumentsTrait;
    
    private $skip = 0;
    private $take = 50; // Cannot take more than 50 at a time
    private $tagFolders = ['Preferred Channel', 'Communication Restrictions', 'Email Interest Type', 'Custom Email Interests', 'Metro Area', 'Source Code'];
    private $tags;
    private $badAddressTag;
    
    public function sync($date)
    {
        ini_set('max_execution_time', 600);
        
        $this->syncHouseholds($date);
        $this->syncContacts($date);
        $this->syncPurposes();
        $this->syncCampaigns();
        $this->syncAppeals();
        $this->syncTransactions($date);
        $this->syncRelationships();
        $this->syncNotes($date);
        $this->syncInteractions($date);
        $this->syncSoftCredits();
        $this->syncTransactionAddons();

        // These should not be needed since the stuff here is already added on the above methods, check function description for details
//        $this->syncPaymentOptionsAndChannels();
//        $this->removeRecurringSchedule();
    }
    
    public function syncHouseholds($date)
    {
        $total = $this->getHouseholds(0, 0, $date);
        $totalHouseholds = array_get($total, 'TotalFiltered');
        
        if ($totalHouseholds === 0) {
            return 'No new households';
        }
        
        for ($i = $this->skip; $i < $totalHouseholds; $i+=50) {
            $bloomerangHouseholds = $this->getHouseholds($i, $this->take, $date);
            
            if (array_get($bloomerangHouseholds, 'ResultCount') > 0) {
                foreach (array_get($bloomerangHouseholds, 'Results') as $household) {
                    $this->importHousehold($household);
                }
            }
        }
    }
    
    public function importHousehold($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Family::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $familyData = [
            'name' => array_get($bloomerangData, 'FullName'),
            'envelope_name' => array_get($bloomerangData, 'EnvelopeName'),
            'tenant_id' => auth()->user()->tenant_id
        ];
        
        $family = mapModel(new Family(), $familyData);
        $family->save();
        
        $this->alternativeIdCreate(array_get($family, 'id'), get_class($family), [
            'alt_id' => $altId,
            'label' => array_get($family, 'name'),
            'system_created_by' => 'Bloomerang'
        ]);
    }
    
    public function syncContacts($date) 
    {
        $this->createTags();
        $this->loadTags();
        
        $total = $this->getConstituents(0, 0, $date);
        $totalContacts = array_get($total, 'TotalFiltered');
        
        if ($totalContacts === 0) {
            return 'No new contacts';
        }
        
        for ($i = $this->skip; $i < $totalContacts; $i+=50) {
            $bloomerangContacts = $this->getConstituents($i, $this->take, $date);
            
            if (array_get($bloomerangContacts, 'ResultCount') > 0) {
                foreach (array_get($bloomerangContacts, 'Results') as $contact) {
                    $this->importContact($contact);
                }
            }
        }
    }
    
    public function importContact($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $contactData = [
            'first_name' => nullIfEmpty(array_get($bloomerangData, 'FirstName')),
            'last_name' => nullIfEmpty(array_get($bloomerangData, 'LastName')),
            'middle_name' => nullIfEmpty(array_get($bloomerangData, 'MiddleName')),
            'salutation' => nullIfEmpty(array_get($bloomerangData, 'Prefix')),
            'website' => nullIfEmpty(array_get($bloomerangData, 'Website')),
            'facebook_id' => nullIfEmpty(array_get($bloomerangData, 'FacebookId')),
            'twitter_id' => nullIfEmpty(array_get($bloomerangData, 'TwitterId')),
            'linkedin_id' => nullIfEmpty(array_get($bloomerangData, 'LinkedInId')),
            'gender' => nullIfEmpty(array_get($bloomerangData, 'Gender')),
            'dob' => array_get($bloomerangData, 'Birthdate'),
            'email_1' => array_get($bloomerangData, 'PrimaryEmail.Value'),
            'unsubscribed' => array_get($bloomerangData, 'EmailInterestType') === 'OptedOut' ? date('Y-m-d H:i:s') : null,
            'active' => array_get($bloomerangData, 'Status') === 'Active' ? 1 : 0,
            'position' => nullIfEmpty(array_get($bloomerangData, 'JobTitle')),
            'company' => nullIfEmpty(array_get($bloomerangData, 'Employer')),
            'type' => array_get($bloomerangData, 'Type') === 'Organization' ? 'organization' : 'person',
            'preferred_name' => null,
            'cell_phone' => nullIfEmpty(array_get($bloomerangData, 'PrimaryPhone.Number'))
        ];
        
        if (array_get($bloomerangData, 'Type') === 'Organization') {
            $contactData['company'] = nullIfEmpty(array_get($bloomerangData, 'FullName'));
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Contact::class);
        
        // Contact does not exist so we make a new one, else just update existing
        if (empty($altIdObject)) {
            // Check first if we already have a contact with same email, if yes than use that
            if (!empty(array_get($contactData, 'email_1'))) {
                $contact = Contact::where('email_1', array_get($contactData, 'email_1'))->first();
                
                if (!empty($contact)) {
                    // Check if existing contact was imported before from Bloomerang
                    // Doing this because email in Bloomerang might not be unique
                    // This way a second contact with same email does not overwrite the first one
                    $altIdContact = $contact->altIds()->where('system_created_by', 'Bloomerang')->first();
                    if (!empty($altIdContact)) {
                        $contact = null; // Force new contact creation
                        $contactData['email_1'] = null; // Unset email 1
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
                'system_created_by' => 'Bloomerang'
            ]);
        } else {
            $contact = Contact::find(array_get($altIdObject, 'relation_id'));
            
            if ($contact) {
                mapModel($contact, $contactData);
                $contact->update();
            }
        }
        
        if ($contact) {
            $this->importContactAddresses($contact, $altId);
            
            $this->importContactPhones($contact, $altId);
            
            $this->importContactEmails($contact, $altId);
            
            //$this->importProfileImage($contact, array_get($bloomerangData, 'CroppedCustomProfileImageUrl', array_get($bloomerangData, 'FullCustomProfileImageUrl')));

            $this->importContactFamily($contact, array_get($bloomerangData, 'HouseholdId'), array_get($bloomerangData, 'IsHeadOfHousehold'));

            $this->addTags($contact, $bloomerangData);
        }
    }
    
    public function importContactAddresses(Contact $contact, $altId)
    {
        $addresses = $this->getAddresses(0, 50, $altId);
        
        if (array_get($addresses, 'ResultCount') > 0) {
            $contact->addresses()->delete();
            
            foreach (array_get($addresses, 'Results') as $bloomerangAddress) {
                $this->importContactAddress($contact, $bloomerangAddress);
            }
        }
    }
    
    public function importContactAddress(Contact $contact, $bloomerangAddress)
    {
        if ($bloomerangAddress) {
            $addressData = [
                'mailing_address_1' => array_get($bloomerangAddress, 'IsBad') ? null : nullIfEmpty(array_get($bloomerangAddress, 'Street')),
                'city' => nullIfEmpty(array_get($bloomerangAddress, 'City')),
                'region' => nullIfEmpty(array_get($bloomerangAddress, 'StateAbbreviation')),
                'postal_code' => nullIfEmpty(array_get($bloomerangAddress, 'PostalCode')),
                'country' => nullIfEmpty(array_get($bloomerangAddress, 'CountryCode')),
                'is_residence' => array_get($bloomerangAddress, 'Type') === 'Home' ? 1 : 0,
                'is_mailing' => array_get($bloomerangAddress, 'IsPrimary') ? 1 : 0,
                'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                'relation_id' => array_get($contact, 'id'),
                'relation_type' => Contact::class
            ];

            $address = mapModel(new Address(), $addressData);
            $address->save();
            
            $contact->tags()->detach([array_get($this->badAddressTag, 'id')]);
            
            if (array_get($bloomerangAddress, 'IsBad')) {
                $contact->tags()->attach([array_get($this->badAddressTag, 'id')]);
            }
        }
    }
    
    public function importContactPhones(Contact $contact, $altId)
    {
        $phones = $this->getPhones(0, 50, $altId);
        
        $phoneData = [
            'Mobile' => array_get($contact, 'cell_phone'),
            'Home' => null,
            'Work' => null,
            'Other' => []
        ];
        
        if (array_get($phones, 'ResultCount') > 0) {
            foreach (array_get($phones, 'Results') as $bloomerangPhone) {
                if (!array_get($bloomerangPhone, 'IsPrimary')) {
                    $this->getPhone($phoneData, $bloomerangPhone);
                }
            }
        }
        
        $contact->cell_phone = array_get($phoneData, 'Mobile');
        $contact->home_phone = array_get($phoneData, 'Home');
        $contact->work_phone = array_get($phoneData, 'Work');
        $contact->other_phone = implode(',', array_get($phoneData, 'Other'));
        $contact->update();
    }
    
    public function importContactEmails(Contact $contact, $altId)
    {
        $emails = $this->getEmails(0, 50, $altId);
        
        $email2 = [];
        
        if (array_get($emails, 'ResultCount') > 0) {
            foreach (array_get($emails, 'Results') as $bloomerangEmail) {
                if (!array_get($bloomerangEmail, 'IsPrimary')) {
                    $email2[] = array_get($bloomerangEmail, 'Value');
                }
            }
        }
        
        if (!empty($email2)) {
            $contact->email_2 = implode(',', $email2);
            $contact->update();
        }
    }
    
    public function importProfileImage(Contact $contact, $url)
    {
        if (!empty($url)) {
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
                        $contact->profile_image = 'profile_images/'.$filename;
                    } else {
                        if (!empty($contact->profile_image)) {
                            checkAndDeleteFile(storage_path('app/public/contacts/' . $contact->profile_image));
                        }
                        
                        $imageResize->save(storage_path('app/public/contacts/'.$filename));
                        $contact->profile_image = $filename;
                    }
                }

                $contact->update();
            }
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
    
    public function addTags(Contact $contact, $bloomerangData) 
    {
        $tagsToAdd = [];
        
        $contact->tags()->detach($this->tags);
        
        $preferredChannel = array_get($bloomerangData, 'PreferredCommunicationChannel');
        
        if (!empty($preferredChannel)) {
            switch ($preferredChannel) {
                case 'TextMessage':
                    $tagsToAdd[] = $this->tags['Text Message'];
                    break;
                default :
                    $tagsToAdd[] = $this->tags[$preferredChannel];
                    break;
            }
        }
        
        $communicationRestrictions = array_get($bloomerangData, 'CommunicationRestrictions');
        
        if (!empty($communicationRestrictions)) {
            if (in_array('DoNotCall', $communicationRestrictions)) {
                $tagsToAdd[] = $this->tags['Do Not Call'];
            }
            if (in_array('DoNotMail', $communicationRestrictions)) {
                $tagsToAdd[] = $this->tags['Do Not Mail'];
            }
            if (in_array('DoNotSolicit', $communicationRestrictions)) {
                $tagsToAdd[] = $this->tags['Do Not Solicit'];
            }
        }
        
        $emailInterest = array_get($bloomerangData, 'EmailInterestType');
        
        if (!empty($emailInterest)) {
            switch ($emailInterest) {
                case 'All':
                    $tagsToAdd[] = $this->tags['All Emails'];
                    break;
                case 'Custom':
                    $tagsToAdd[] = $this->tags['Custom Email Interest'];
                    break;
                case 'OptedOut':
                    $tagsToAdd[] = $this->tags['Opted Out'];
                    break;
            }
        }
        
        $customEmailInterests = array_get($bloomerangData, 'CustomEmailInterests');
        
        if (!empty($customEmailInterests)) {
            foreach ($customEmailInterests as $interest) {
                $this->importTag(array_get($interest, 'Name'), Folder::where('type', 'TAGS')->where('name', 'Custom Email Interests')->first());
                $tagsToAdd[] = $this->tags[array_get($interest, 'Name')];
            }
        }
        
        $customValues = array_get($bloomerangData, 'CustomValues');
        
        if (!empty($customValues)) {
            foreach ($customValues as $custom) {
                if (array_get($custom, 'FieldId') == 3198977) { // TODO - Make this dynamic for Metro Area
                    foreach (array_get($custom, 'Values') as $value) {
                        $this->importTag(array_get($value, 'Value'), Folder::where('type', 'TAGS')->where('name', 'Metro Area')->first());
                        $tagsToAdd[] = $this->tags[array_get($value, 'Value')];
                    }
                }
                
                if (array_get($custom, 'FieldId') == 17814528) { // TODO - Make this dynamic for Source Code
                    $this->importTag(array_get($custom, 'Value.Value'), Folder::where('type', 'TAGS')->where('name', 'Source Code')->first());
                    $tagsToAdd[] = $this->tags[array_get($custom, 'Value.Value')];
                    
                    if (strpos(array_get($contact, 'background_info'), array_get($custom, 'Value.Value')) === false) {
                        $contact->background_info = trim(array_get($contact, 'background_info').' | Source Code: '.array_get($custom, 'Value.Value').' |');
                        $contact->update();
                    }
                }
                
                if (array_get($custom, 'FieldId') == 3156993) { // TODO - Make this dynamic for Notes
                    $value = array_get($custom, 'Value.Value');
                    
                    if (strpos(array_get($contact, 'background_info'), $value) === false) {
                        $contact->background_info = trim(array_get($contact, 'background_info').' '.$value);
                        $contact->update();
                    }
                    
                    // We need to delete the note because the should go to background info
                    $note = Note::where('relation_id', array_get($contact, 'id'))->where('title', $value)->whereNull('content')->first();
                    
                    if ($note) {
                        $note->delete();
                    }
                }
            }
        }
        
        if ($tagsToAdd) {
            $contact->tags()->attach($tagsToAdd);
        }
    }
    
    public function importTag($name, $folder)
    {
        if (!array_key_exists($name, $this->tags) && $folder) {
            $tag = new Tag();
            $tag->name = $name;
            $tag->folder_id = array_get($folder, 'id');
            $tag->tenant_id = auth()->user()->tenant_id;
            $tag->save();
            
            $this->tags[$name] = array_get($tag, 'id');
        }
    }
    
    public function syncPurposes()
    {
        $total = $this->getFunds(0, 0);
        $totalPurposes = array_get($total, 'TotalFiltered');
        
        if ($totalPurposes === 0) {
            return 'No new purposes';
        }
        
        for ($i = 0; $i < $totalPurposes; $i+=50) {
            $bloomerangPurposes = $this->getFunds($i, $this->take);
            
            if (array_get($bloomerangPurposes, 'ResultCount') > 0) {
                foreach (array_get($bloomerangPurposes, 'Results') as $purpose) {
                    $this->importPurpose($purpose);
                }
            }
        }
    }
    
    public function importPurpose($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
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
            'name' => nullIfEmpty(array_get($bloomerangData, 'Name')),
            'receive_donations' => 1,
            'page_type' => 'project',
            'sub_type' => 'projects',
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
                'system_created_by' => 'Bloomerang'
            ]);
        } else {
            $purpose = Purpose::find(array_get($altIdObject, 'relation_id'));
            
            if ($purpose) {
                mapModel($purpose, $purposeData);
                $purpose->update();
            }
        }
    }
    
    public function syncCampaigns()
    {
        $total = $this->getCampaigns(0, 0);
        $totalCampaigns = array_get($total, 'TotalFiltered');
        
        if ($totalCampaigns === 0) {
            return 'No new campaigns';
        }
        
        for ($i = 0; $i < $totalCampaigns; $i+=50) {
            $bloomerangCampaigns = $this->getCampaigns($i, $this->take);
            
            if (array_get($bloomerangCampaigns, 'ResultCount') > 0) {
                foreach (array_get($bloomerangCampaigns, 'Results') as $campaign) {
                    $this->importCampaign($campaign);
                }
            }
        }
    }
    
    public function importCampaign($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $purpose = Purpose::where('sub_type', 'organizations')->first();
        
        $campaignData = [
            'purpose_id' => array_get($purpose, 'id'),
            'name' => nullIfEmpty(array_get($bloomerangData, 'Name')),
            'receive_donations' => 1,
            'page_type' => 'givingpage',
            'sub_type' => 'givingpages',
            'goal' => nullIfEmpty(array_get($bloomerangData, 'Goal'))
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
                'system_created_by' => 'Bloomerang'
            ]);
        } else {
            $campaign = Campaign::find(array_get($altIdObject, 'relation_id'));
            
            if ($campaign) {
                mapModel($campaign, $campaignData);
                $campaign->update();
            }
        }
    }
    
    public function syncAppeals()
    {
        $total = $this->getAppeals(0, 0);
        $totalAppeals = array_get($total, 'TotalFiltered');
        
        if ($totalAppeals === 0) {
            return 'No new appeals';
        }
        
        for ($i = 0; $i < $totalAppeals; $i+=50) {
            $bloomerangAppeals = $this->getAppeals($i, $this->take);
            
            if (array_get($bloomerangAppeals, 'ResultCount') > 0) {
                foreach (array_get($bloomerangAppeals, 'Results') as $appeal) {
                    $this->importAppeal($appeal);
                }
            }
        }
    }
    
    public function importAppeal($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
                
        $altIdObject = $this->alternativeIdRetrieve($altId, Tag::class);
        
        if (empty($altIdObject)) {
            $tag = new Tag();
            $tag->name = 'Appeal - '.array_get($bloomerangData, 'Name');
            $tag->folder_id = 1;
            $tag->tenant_id = auth()->user()->tenant_id;
            $tag->save();
            
            $this->alternativeIdCreate(array_get($tag, 'id'), get_class($tag), [
                'alt_id' => $altId,
                'label' => array_get($tag, 'name'),
                'system_created_by' => 'Bloomerang'
            ]);
        } else {
            $tag = Tag::find(array_get($altIdObject, 'relation_id'));
            
            if ($tag) {
                $tag->name = 'Appeal - '.array_get($bloomerangData, 'Name');
                $tag->update();
            }
        }
    }
    
    public function syncTransactions($date)
    {
        $total = $this->getTransactions(0, 0);
        $totalTransactions = array_get($total, 'TotalFiltered');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        $stop = false;
        
        for ($i = 0; $i < $totalTransactions && !$stop; $i+=50) {
            $bloomerangTransactions = $this->getTransactions($i, $this->take);
            
            if (array_get($bloomerangTransactions, 'ResultCount') > 0) {
                foreach (array_get($bloomerangTransactions, 'Results') as $transaction) {
                    if ($date && strtotime($this->getDate(array_get($transaction, 'AuditTrail.CreatedDate'))) < strtotime($date)) {
                        $stop = true;
                        break;
                    }
                    
                    $this->importTransaction($transaction);
                }
            }
        }
    }
    
    public function importTransaction($bloomerangData)
    {
        // If type is RecurringDonation it means it's just a recurring donation schedule not an actual payment so we don't get those
        if (array_get($bloomerangData, 'Designations.0.Type') === 'RecurringDonation') {
            return false;
        }
        
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        $transactionTime = nullIfEmpty(array_get($bloomerangData, 'Date').' 12:00:00');
        if (!is_null($transactionTime)) {
            // TODO - Find a better way to handle localization according to tenant local time
            $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
        }
        
        $transactionTemplateData = [
            'completion_datetime' => $transactionTime,
            'amount' => array_get($bloomerangData, 'Amount'),
            'is_recurring' => (array_get($bloomerangData, 'Designations.0.Type') === 'RecurringDonationPayment' || array_get($bloomerangData, 'Designations.0.Type') === 'RecurringDonation') ? 1 : 0,
            'is_pledge' => 0,
            'successes' => 1,
            'acknowledged' => array_get($bloomerangData, 'Designations.0.AcknowledgementStatus') === 'Yes' ? 1 : 0
        ];
        
        $transactionData = [
            'transaction_initiated_at' => $transactionTime,
            'transaction_last_updated_at' => $transactionTime,
            'channel' => $this->getChannel($bloomerangData),
            'check_number' => nullIfEmpty(onlyNumbers(array_get($bloomerangData, 'CheckNumber'))),
            'system_created_by' => 'Bloomerang',
            'status' => 'complete',
            'transaction_path' => 'bloomerang',
            'anonymous_amount' => 'protected',
            'anonymous_identity' => 'protected',
            'type' => 'donation',
            'acknowledged' => array_get($bloomerangData, 'Designations.0.AcknowledgementStatus') === 'Yes' ? 1 : 0,
            'acknowledged_at' => array_get($bloomerangData, 'Designations.0.AcknowledgementStatus') === 'Yes' ? $transactionTime : null,
            'tax_deductible' => array_get($bloomerangData, 'NonDeductibleAmount') > 0 ? 0 : 1,
            'deposit_date' => $this->getDepositDate($bloomerangData),
            'comment' => array_get($bloomerangData, 'Designations.0.Note')
        ];
        
        $contactAltid = array_get($bloomerangData, 'AccountId');
        if (!empty($contactAltid)) {
            $contactAltidObject = $this->alternativeIdRetrieve($contactAltid, Contact::class);
            if (!empty($contactAltidObject)) {
                $transactionTemplateData['contact_id'] = array_get($contactAltidObject, 'relation_id');
                $transactionData['contact_id'] = array_get($contactAltidObject, 'relation_id');
            }
        }
        
        $transactionSplitData = [];
        
        foreach (array_get($bloomerangData, 'Designations') as $split) {
            $purposeId = $this->getPurposeId($split);
            $campaignId = $this->getCampaignId($split);
            
            $transactionSplitData[] = [
                'template' => [
                    'campaign_id' => $campaignId,
                    'purpose_id' => $purposeId,
                    'tax_deductible' => array_get($split, 'NonDeductibleAmount') > 0 ? 0 : 1,
                    'type' => 'donation',
                    'amount' => nullIfEmpty(array_get($split, 'Amount')),
                    'splitAltId' => array_get($split, 'Id'),
                    'appealData' => array_get($split, 'Appeal')
                ],
                'transaction' => [
                    'campaign_id' => $campaignId,
                    'purpose_id' => $purposeId,
                    'amount' => nullIfEmpty(array_get($split, 'Amount')),
                    'type' => 'donation',
                    'tax_deductible' => array_get($split, 'NonDeductibleAmount') > 0 ? 0 : 1,
                    'splitAltId' => array_get($split, 'Id'),
                    'appealData' => array_get($split, 'Appeal')
                ]
            ];
        }
        
        // Transaction does not exist so we make a new one, else do nothing
        if (empty($altIdObject)) {
            if ($transactionData['contact_id']) {
                $transactionData['payment_option_id'] = $this->getPaymentOptionId($transactionData['contact_id'], $bloomerangData);
            }
            
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
            
            foreach (array_get($bloomerangData, 'Designations') as $split) {
                if (array_get($split, 'AttachmentIds')) {
                    $this->importAttachments(array_get($transaction, 'id'), array_get($split, 'AttachmentIds'));
                }
            }
        }
    }
    
    public function getPurposeId($split)
    {
        $parentPurpose = Purpose::where('sub_type', 'organizations')->first();
        
        if (empty(array_get($split, 'Fund'))) {
            return array_get($parentPurpose, 'id');
        }
        
        $accountingIntegrationCoa = array_get($split, 'Fund.Id');
        
        if (empty($accountingIntegrationCoa)) {
            return array_get($parentPurpose, 'id');
        }
        
        $purpose = Purpose::where('accounting_integration_coa', $accountingIntegrationCoa)->first();
        
        if (empty($purpose)) {
            return array_get($parentPurpose, 'id');
        } else {
            return array_get($purpose, 'id');
        }
    }
    
    public function getCampaignId($split)
    {
        if (empty(array_get($split, 'Campaign'))) {
            return 1;
        }
        
        $campaingId = array_get($split, 'Campaign.Id');
        
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
    
    public function getPaymentOptionId($contactId, $bloomerangData)
    {
        if (!$contactId) {
            return null;
        }
        
        $paymentOptionData = ['contact_id' => $contactId];
        
        switch (array_get($bloomerangData, 'Method')) {
            case 'Cash':
                $paymentOptionData['category'] = 'cash';
                $paymentOptionData['last_four'] = null;
                break;
            case 'Check':
                $paymentOptionData['category'] = 'check';
                $paymentOptionData['last_four'] = '****';
                break;
            case 'CreditCard':
                $paymentOptionData['category'] = 'cc';
                $paymentOptionData['last_four'] = array_get($bloomerangData, 'CreditCardLastFourDigits', '****');
                break;
            case 'Eft':
                $paymentOptionData['category'] = 'ach';
                $paymentOptionData['last_four'] = array_get($bloomerangData, 'EftLastFourDigits', '****');
                break;
            case 'InKind':
                if (array_get($bloomerangData, 'InKindType') === 'Goods') {
                    $paymentOptionData['category'] = 'goods';
                    $paymentOptionData['last_four'] = null;
                } else {
                    $paymentOptionData['category'] = 'other';
                    $paymentOptionData['last_four'] = null;
                }
                break;
            default :
                $paymentOptionData['category'] = 'unknown';
                $paymentOptionData['last_four'] = null;
                break;
        }
        
        if ($paymentOptionData['last_four'] === '') {
            $paymentOptionData['last_four'] = '****';
        }
        
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
    
    public function getChannel($bloomerangData)
    {
        $bloomerangChannel = null;
        
        if (array_get($bloomerangData, 'Designations.0.CustomValues')) {
            foreach (array_get($bloomerangData, 'Designations.0.CustomValues') as $field) {
                if (array_get($field, 'FieldId') === 24576) { // TODO - find a way to get the channel automatically
                    $bloomerangChannel = array_get($field, 'Value.Value');
                }
            }
        }
        
        switch ($bloomerangChannel) {
            case 'Appreciated Stock through NCF':
                $channel = 'ncf';
                break;
            case 'Event':
                $channel = 'event';
                break;
            case 'Face-to-Face':
                $channel = 'face_to_face';
                break;
            case 'FavorIntl.org Website':
                $channel = 'website';
                break;
            case 'Mail':
                $channel = 'mail';
                break;
            case 'Vanco':
            case 'Other':
                $channel = 'other';
                break;
            default :
                $channel = 'unknown';
                break;
        }
        
        return $channel;
    }
    
    public function getDepositDate($bloomerangData)
    {
        $date = null;
        
        if (array_get($bloomerangData, 'Designations.0.CustomValues')) {
            foreach (array_get($bloomerangData, 'Designations.0.CustomValues') as $field) {
                if (array_get($field, 'FieldId') === 326656) { // TODO - find a way to get the deposit date automatically
                    if (array_get($field, 'Value.Value')) {
                        $date = Carbon::createFromFormat('m/d/Y', array_get($field, 'Value.Value'))->format('Y-m-d');
                    }
                }
            }
        }
        
        return $date;
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
            'system_created_by' => 'Bloomerang'
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
            'system_created_by' => 'Bloomerang'
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
            'system_created_by' => 'Bloomerang'
        ]);
        
        if (array_get($data, 'appealData')) {
            $this->tagAppeal($split, array_get($data, 'appealData.Name'));
        }
        
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
            'system_created_by' => 'Bloomerang'
        ]);
        
        if (array_get($data, 'appealData')) {
            $this->tagAppeal($split, array_get($data, 'appealData.Name'));
        }
        
        return $split;
    }
    
    public function syncRelationships()
    {
        $altIds = AltId::where('relation_type', Contact::class)->where('system_created_by', 'Bloomerang')->get();
        
        if ($altIds) {
            foreach ($altIds as $altId) {
                $this->importRelationships($altId);
            }
        }
    }
    
    public function importRelationships(AltId $altId)
    {
        $bloomerangRelations = $this->getRelationships(array_get($altId, 'alt_id'));

        if (array_get($bloomerangRelations, 'ResultCount') > 0) {
            $contact = Contact::with(['relatives', 'relativesUp'])->find(array_get($altId, 'relation_id'));
            
            if ($contact) {
                foreach (array_get($bloomerangRelations, 'Results') as $relation) {
                    $this->importRelationship($altId, $contact, $relation);
                }
            }
        }
    }
    
    public function importRelationship(Altid $altId, Contact $contact, $relation)
    {
        $relativeAltId = array_get($relation, 'AccountId1') == array_get($altId, 'alt_id') ? array_get($relation, 'AccountId2') : array_get($relation, 'AccountId1');
        $relativeAltIdObject = AltId::where('alt_id', $relativeAltId)->where('relation_type', Contact::class)->where('system_created_by', 'Bloomerang')->first();

        if (!empty($relativeAltIdObject)) {
            // check if we already have this relation
            foreach ($contact->relatives as $relative) {
                if (array_get($relative, 'id') == array_get($relativeAltIdObject, 'relation_id')) {
                    return false;
                }
            }
            
            foreach ($contact->relativesUp as $relative) {
                if (array_get($relative, 'id') == array_get($relativeAltIdObject, 'relation_id')) {
                    return false;
                }
            }
            
            $sync = [
                array_get($relativeAltIdObject, 'relation_id') => [
                    'contact_relationship' => array_get($relation, 'AccountId1') == array_get($altId, 'alt_id') ? array_get($relation, 'RelationshipRole1.Name') : array_get($relation, 'RelationshipRole2.Name'),
                    'relative_relationship' => array_get($relation, 'AccountId1') == array_get($altId, 'alt_id') ? array_get($relation, 'RelationshipRole2.Name') : array_get($relation, 'RelationshipRole1.Name')
                ]
            ];
            $contact->relatives()->sync($sync, false);
        }
    }
    
    public function syncNotes($date)
    {
        $total = $this->getNotes(0, 0);
        $totalNotes = array_get($total, 'TotalFiltered');
        
        if ($totalNotes === 0) {
            return 'No new notes';
        }
        
        $stop = false;
        
        for ($i = $this->skip; $i < $totalNotes && !$stop; $i+=50) {
            $bloomerangNotes = $this->getNotes($i, $this->take);
            
            if (array_get($bloomerangNotes, 'ResultCount') > 0) {
                foreach (array_get($bloomerangNotes, 'Results') as $note) {
                    if ($date && strtotime($this->getDate(array_get($note, 'AuditTrail.CreatedDate'))) < strtotime($date)) {
                        $stop = true;
                        break;
                    }
                    
                    $this->importNote($note);
                }
            }
        }
    }
         
    public function importNote($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // check if note already imported
        $altIdObject = $this->alternativeIdRetrieve($altId, Note::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        // check if contact has been imported
        $altIdContactObject = $this->alternativeIdRetrieve(array_get($bloomerangData, 'AccountId'), Contact::class);
        
        if (empty($altIdContactObject)) {
            return false;
        }
        
        $note = new Note();
        $note->user_id = array_get(auth()->user(), 'id');
        $note->relation_id = array_get($altIdContactObject, 'relation_id');
        $note->relation_type = 'App\Models\Contact';
        $note->title = 'Bloomerang note';
        $note->content = array_get($bloomerangData, 'Note');
        $note->date = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        $note->created_at = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        $note->updated_at = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        
        auth()->user()->tenant->notes()->save($note);
        
        $note->refresh();
        
        $this->alternativeIdCreate(array_get($note, 'id'), get_class($note), [
            'alt_id' => $altId,
            'label' => 'Note',
            'system_created_by' => 'Bloomerang'
        ]);
    }
    
    public function syncInteractions($date)
    {
        $total = $this->getInteractions(0, 0);
        $totalNotes = array_get($total, 'TotalFiltered');
        
        if ($totalNotes === 0) {
            return 'No new notes';
        }
        
        $stop = false;
        
        for ($i = $this->skip; $i < $totalNotes && !$stop; $i+=50) {
            $bloomerangNotes = $this->getInteractions($i, $this->take);
            
            if (array_get($bloomerangNotes, 'ResultCount') > 0) {
                foreach (array_get($bloomerangNotes, 'Results') as $note) {
                    if ($date && strtotime($this->getDate(array_get($note, 'AuditTrail.CreatedDate'))) < strtotime($date)) {
                        $stop = true;
                        break;
                    }
                    
                    $this->importInteraction($note);
                }
            }
        }
    }
    
    public function importInteraction($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        // check if note already imported
        $altIdObject = $this->alternativeIdRetrieve($altId, Note::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        // check if contact has been imported
        $altIdContactObject = $this->alternativeIdRetrieve(array_get($bloomerangData, 'AccountId'), Contact::class);
        
        if (empty($altIdContactObject)) {
            return false;
        }
        
        $title = '';
        if (array_get($bloomerangData, 'Channel') !== 'Other') {
            $title.= array_get($bloomerangData, 'Channel').' - ';
        }
        if (array_get($bloomerangData, 'Purpose') !== 'Other') {
            $title.= array_get($bloomerangData, 'Purpose').' - ';
        }
        $title.= array_get($bloomerangData, 'Subject');
        
        $note = new Note();
        $note->user_id = array_get(auth()->user(), 'id');
        $note->relation_id = array_get($altIdContactObject, 'relation_id');
        $note->relation_type = 'App\Models\Contact';
        $note->title = $title;
        $note->content = array_get($bloomerangData, 'Note');
        $note->date = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        $note->created_at = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        $note->updated_at = $this->getDate(array_get($bloomerangData, 'AuditTrail.CreatedDate'));
        
        auth()->user()->tenant->notes()->save($note);
        
        $note->refresh();
        
        $this->alternativeIdCreate(array_get($note, 'id'), get_class($note), [
            'alt_id' => $altId,
            'label' => 'Note',
            'system_created_by' => 'Bloomerang'
        ]);
    }
    
    public function syncSoftCredits()
    {
        $total = $this->getSoftCredits(0, 0);
        $totalSoftCredits = array_get($total, 'TotalFiltered');
        
        if ($totalSoftCredits === 0) {
            return 'No new transactions';
        }
        
        for ($i = 0; $i < $totalSoftCredits; $i+=50) {
            $bloomerangSoftCredits = $this->getSoftCredits($i, $this->take);
            
            if (array_get($bloomerangSoftCredits, 'ResultCount') > 0) {
                foreach (array_get($bloomerangSoftCredits, 'Results') as $softCredit) {
                    $this->importSoftCredit($softCredit);
                }
            }
        }
    }
    
    public function importSoftCredit($bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        // check if parent transaction has been imported
        $altIdParentTransactionObject = $this->alternativeIdRetrieve(array_get($bloomerangData, 'TransactionId'), Transaction::class);
        
        if (empty($altIdParentTransactionObject)) {
            return false;
        }
        
        $parentTransaction = Transaction::find(array_get($altIdParentTransactionObject, 'relation_id'));
        
        if (empty($parentTransaction)) {
            return false;
        }
        
        $parentTransactionTemplate = TransactionTemplate::find(array_get($parentTransaction, 'transaction_template_id'));
        
        $transactionTemplateData = [
            'completion_datetime' => array_get($parentTransactionTemplate, 'completion_datetime'),
            'amount' => nullIfEmpty(array_get($bloomerangData, 'Amount')),
            'is_recurring' => array_get($parentTransactionTemplate, 'is_recurring'),
            'is_pledge' => array_get($parentTransactionTemplate, 'is_pledge'),
            'successes' => array_get($parentTransactionTemplate, 'successes'),
            'tax_deductible' => 0,
            'acknowledged' => array_get($parentTransactionTemplate, 'acknowledged')
        ];
        
        $transactionData = [
            'transaction_initiated_at' => array_get($parentTransaction, 'transaction_initiated_at'),
            'transaction_last_updated_at' => array_get($parentTransaction, 'transaction_last_updated_at'),
            'channel' => array_get($parentTransaction, 'channel'),
            'system_created_by' => 'Bloomerang',
            'status' => array_get($parentTransaction, 'status'),
            'transaction_path' => 'bloomerang',
            'anonymous_amount' => array_get($parentTransaction, 'anonymous_amount'),
            'anonymous_identity' => array_get($parentTransaction, 'anonymous_identity'),
            'type' => array_get($parentTransaction, 'type'),
            'acknowledged' => array_get($parentTransaction, 'acknowledged'),
            'acknowledged_at' => array_get($parentTransaction, 'acknowledged_at'),
            'parent_transaction_id' => array_get($parentTransaction, 'id')
        ];
        
        $contactAltid = array_get($bloomerangData, 'AccountId');
        
        if (empty($contactAltid)) {
            return false;
        }
        
        $contactAltidObject = $this->alternativeIdRetrieve($contactAltid, Contact::class);
        
        if (empty($contactAltidObject)) {
            return false;
        }
        
        $transactionTemplateData['contact_id'] = array_get($contactAltidObject, 'relation_id');
        $transactionData['contact_id'] = array_get($contactAltidObject, 'relation_id');
        
        $parentTransactionTemplateSplit = TransactionTemplateSplit::where('transaction_template_id', array_get($parentTransactionTemplate, 'id'))->first();
        
        $transactionTemplateSplitData = [
            'campaign_id' => array_get($parentTransactionTemplateSplit, 'campaign_id'),
            'purpose_id' => array_get($parentTransactionTemplateSplit, 'purpose_id'),
            'tax_deductible' => 0,
            'type' => array_get($parentTransactionTemplateSplit, 'type'),
            'amount' => nullIfEmpty(array_get($bloomerangData, 'Amount')),
            'splitAltId' => $altId
        ];
        
        $parentTransactionSplit = TransactionSplit::where('transaction_id', array_get($parentTransaction, 'id'))->first();
        
        $transactionSplitData = [
            'campaign_id' => array_get($parentTransactionSplit, 'campaign_id'),
            'purpose_id' => array_get($parentTransactionSplit, 'purpose_id'),
            'tax_deductible' => 0,
            'type' => array_get($parentTransactionSplit, 'type'),
            'amount' => nullIfEmpty(array_get($bloomerangData, 'Amount')),
            'splitAltId' => $altId
        ];
        
        // Transaction does not exist so we make a new one, else do nothing
        if (empty($altIdObject)) {
            $transactionTemplate = $this->storeTransactionTemplate($transactionTemplateData, $altId);
            
            $transactionData['transaction_template_id'] = array_get($transactionTemplate, 'id');
            
            $transaction = $this->storeTransaction($transactionData, $altId);
            
            $transactionTemplateSplitData['transaction_template_id'] = array_get($transactionTemplate, 'id');
            $splitTemplate = $this->storeTransactionTemplateSplit($transactionTemplateSplitData);

            $transactionSplitData['transaction_id'] = array_get($transaction, 'id');
            $transactionSplitData['transaction_template_split_id'] = array_get($splitTemplate, 'id');
            $this->storeTransactionSplit($transactionSplitData);
        }
    }
    
    /**
     * Use this to sync other transaction stuff that can be added to the transaction after it's creation
     * Currently syncs:
     * 1. Attachments
     * 2. Appeals
     * 3. Notes
     */
    public function syncTransactionAddons()
    {
        $total = $this->getTransactions(0, 0);
        $totalTransactions = array_get($total, 'TotalFiltered');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        for ($i = 0; $i < $totalTransactions; $i+=50) {
            $bloomerangTransactions = $this->getTransactions($i, $this->take);
            
            if (array_get($bloomerangTransactions, 'ResultCount') > 0) {
                foreach (array_get($bloomerangTransactions, 'Results') as $bloomerangTransaction) {
                    // If type is RecurringDonation it means it's just a recurring donation schedule not an actual payment so we don't get those
                    if (array_get($bloomerangTransaction, 'Designations.0.Type') === 'RecurringDonation') {
                        continue;
                    }
                    
                    $altId = array_get($bloomerangTransaction, 'Id');
        
                    if (empty($altId)) {
                        continue;
                    }

                    $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);

                    if (empty($altIdObject)) {
                        continue;
                    }
                    
                    foreach (array_get($bloomerangTransaction, 'Designations') as $split) {
                        if (array_get($split, 'AttachmentIds')) {
                            $this->importAttachments(array_get($altIdObject, 'relation_id'), array_get($split, 'AttachmentIds'));
                        }
                        
                        if (array_get($split, 'Appeal')) {
                            $transaction = Transaction::find(array_get($altIdObject, 'relation_id'));
                            
                            if ($transaction) {
                                $transactionSplits = $transaction->splits;
                                
                                foreach ($transactionSplits as $transactionSplit) {
                                    $this->tagAppeal($transactionSplit, array_get($split, 'Appeal.Name'));
                                    $this->tagAppeal($transactionSplit->transactionTemplateSplit, array_get($split, 'Appeal.Name'));
                                }
                            }
                        }
                        
                        if (array_get($split, 'Note')) {
                            $transaction = Transaction::find(array_get($altIdObject, 'relation_id'));
                            
                            if ($transaction && array_get($transaction, 'comment') !== array_get($split, 'Note')) {
                                array_set($transaction, 'comment', array_get($split, 'Note'));
                                $transaction->update();
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function importAttachments($transactionId, $attachments)
    {
        foreach ($attachments as $attachmentId) {
            $bloomerangAttachment = $this->getAttachment($attachmentId);
            $this->storeAttachment($transactionId, $bloomerangAttachment);
        }
    }
    
    public function storeAttachment($transactionId, $bloomerangData)
    {
        $altId = array_get($bloomerangData, 'Id');
        
        if (empty($altId)) {
            return false;
        }
        
        $altIdObject = $this->alternativeIdRetrieve($altId, Document::class);
        
        if (!empty($altIdObject)) {
            return false;
        }
        
        if (empty($transactionId)) {
            return false;
        }
        
        // TODO - Handle this once we support attachments with external url only
        if (empty(array_get($bloomerangData, 'Extension'))) {
            return false;
        }
        
        $file = [
            'url' => array_get($bloomerangData, 'Url'),
            'name' => array_get($bloomerangData, 'Name'),
            'extension' => array_get($bloomerangData, 'Extension'),
        ];
        
        $document = $this->storeDocumentFromUrl($file, 'transaction_attachmemnts');
        
        array_set($document, 'relation_id', $transactionId);
        array_set($document, 'relation_type', Transaction::class);
        $document->update();
        
        $this->alternativeIdCreate(array_get($document, 'id'), get_class($document), [
            'alt_id' => $altId,
            'label' => 'Transaction Attachment',
            'system_created_by' => 'Bloomerang'
        ]);
    }
    
    public function tagAppeal($split, $appeal)
    {
        if (empty($appeal)) {
            return false;
        }
        
        $name = 'Appeal - '.$appeal;
        
        $tag = Tag::where('folder_id', 1)->where('name', $name)->first();
        
        if (empty($tag)) {
            $tag = new Tag();
            $tag->name = $name;
            $tag->folder_id = 1;
            $tag->tenant_id = auth()->user()->tenant_id;
            $tag->save();
        }
        
        $split->tags()->sync([array_get($tag, 'id')], false);
    }
    
    /**
     * Not needed. Use only if you want to re-update the payment option and channel but normally now transactions should already have those on creation.
     */
    public function syncPaymentOptionsAndChannels()
    {
        $total = $this->getTransactions(0, 0);
        $totalTransactions = array_get($total, 'TotalFiltered');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        for ($i = 0; $i < $totalTransactions; $i+=50) {
            $bloomerangTransactions = $this->getTransactions($i, $this->take);
            
            if (array_get($bloomerangTransactions, 'ResultCount') > 0) {
                foreach (array_get($bloomerangTransactions, 'Results') as $bloomerangTransaction) {
                    // If type is RecurringDonation it means it's just a recurring donation schedule not an actual payment so we don't get those
                    if (array_get($bloomerangTransaction, 'Designations.0.Type') === 'RecurringDonation') {
                        continue;
                    }
                    
                    $altId = array_get($bloomerangTransaction, 'Id');
        
                    if (empty($altId)) {
                        continue;
                    }

                    $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);

                    if (empty($altIdObject)) {
                        continue;
                    }
                    
                    $transaction = Transaction::find(array_get($altIdObject, 'relation_id'));
                    
                    if (empty($transaction)) {
                        continue;
                    }
                    
                    $transaction['payment_option_id'] = $this->getPaymentOptionId(array_get($transaction, 'contact_id'), $bloomerangTransaction);
                    
                    if (array_get($bloomerangTransaction, 'Method') === 'Check') {
                        $transaction['check_number'] = nullIfEmpty(onlyNumbers(array_get($bloomerangTransaction, 'CheckNumber')));
                    }
                    
                    $transaction['channel'] = $this->getChannel($bloomerangTransaction);
                    $transaction['deposit_date'] = $this->getDepositDate($bloomerangTransaction);
                    
                    $transaction->update();
                }
            }
        }
    }
    
    /**
     * Not needed. Use only if you added RecurringDonation type by mistake and wont to remove them
     */
    public function removeRecurringSchedule()
    {
        $total = $this->getTransactions(0, 0, 'RecurringDonation');
        $totalTransactions = array_get($total, 'TotalFiltered');
        
        if ($totalTransactions === 0) {
            return 'No new transactions';
        }
        
        for ($i = 0; $i < $totalTransactions; $i+=50) {
            $bloomerangTransactions = $this->getTransactions($i, $this->take, 'RecurringDonation');
            
            if (array_get($bloomerangTransactions, 'ResultCount') > 0) {
                foreach (array_get($bloomerangTransactions, 'Results') as $bloomerangTransaction) {
                    if (array_get($bloomerangTransaction, 'Designations.0.Type') !== 'RecurringDonation') {
                        continue;
                    }
                    
                    $altId = array_get($bloomerangTransaction, 'Id');
        
                    if (empty($altId)) {
                        continue;
                    }

                    $altIdObject = $this->alternativeIdRetrieve($altId, Transaction::class);

                    if (empty($altIdObject)) {
                        continue;
                    }
                    
                    $transaction = Transaction::find(array_get($altIdObject, 'relation_id'));
                    
                    if (empty($transaction)) {
                        continue;
                    }
                    
                    foreach ($transaction->template->splits as $split) {
                        $split->delete();
                    }
                    $transaction->template->delete();
                    foreach ($transaction->splits as $split) {
                        $split->delete();
                    }
                    $transaction->delete();
                }
            }
        }
    }
    
    private function createTags()
    {
        $tags = ['Bad Address'];
        
        foreach ($tags as $name) {
            $tag = Tag::where('folder_id', 1)->where('name', $name)->first();

            if (empty($tag)) {
                $tag = new Tag();
                $tag->name = $name;
                $tag->folder_id = 1;
                $tag->tenant_id = auth()->user()->tenant_id;
                $tag->save();
            }
            
            if ($name === 'Bad Address') {
                $this->badAddressTag = $tag;
            }
        }
    }
    
    private function loadTags()
    {
        $tags = Tag::whereHas('folder', function ($query) {
            $query->whereIn('name', $this->tagFolders);
        })->get();
        
        foreach ($tags as $tag) {
            $this->tags[array_get($tag, 'name')] = array_get($tag, 'id');
        }
    }
    
    private function getPhone(&$data, $primaryPhone)
    {
        $type = array_get($primaryPhone, 'Type');
        $value = array_get($primaryPhone, 'Number');
        
        if (in_array($type, ['Mobile', 'Home', 'Work']) && empty($data[$type])) {
            $data[$type] = $value;
        } else {
            $data['Other'][] = $value;
        }
    }
    
    private function getDate($date)
    {
        return str_replace('Z', '', str_replace('T', ' ', $date));
    }
}
