<?php
// NOTE Add new methods to Ajax/MODULENAMEFeature

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

use App\Classes\Shared\TicketsTemporaryHold;
use App\Classes\MissionPillarsLog;
use App\Models\User;
use App\Models\Contact;
use App\Traits\CountriesTrait;
use App\Traits\Emails\EmailTrait;

use App\MPLog;

use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\Tag;
use App\Models\Family;
use App\Models\Folder;
use App\Models\Form;
use App\Models\Group;
use App\Models\TenantToken;
use App\Models\Campaign;
use App\Models\EmailSent;
use App\Models\Transaction;

class AjaxController extends Controller {

    use CountriesTrait, EmailTrait;

    public function contactsAutocomplete(Request $request) {
        
        $result = [];
        if ($request->has('payment_options') && (bool) array_get($request, 'payment_options')) {
            $result = $this->searchInContactsWithPaymentOptions($request);
        } else {
            $result = $this->searchInContacts($request);
        }

        //$data = ['suggestions' => $result];
        //return response()->json($data);
        return response()->json($result);
    }

    public function publicContactsAutocomplete(Request $request) {
        if (!array_has($request, 'search') || !$request->ajax()) {
            abort(404);
        }
        $search = array_get($request, 'search');
        $contacts = Contact::where('first_name', 'like', "%$search%")
            ->orWhere('last_name', 'like', "%$search%")
            ->orWhere('email_1', 'like', "%$search%")
            ->get();

        $result = collect($contacts)->reduce(function($result, $contacts) {
            $contact['value'] = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . ' (' . array_get($contacts, 'email_1') . ')';
            $contact['data'] = Crypt::encrypt(array_get($contacts, 'id'));
            $contact['id'] = array_get($contacts, 'id');
            array_push($result, $contact);
            return $result;
        }, []);
        return $result;
        //$data = ['suggestions' => $result];
        //return $data;
    }

    private function searchInContactsWithPaymentOptions(Request $request) {
        $search = array_get($request, 'search');
        $contacts = Contact::with(['paymentOptions' => function($query) {
                    $query->orderBy('category', 'desc');
                }])
            ->where('first_name', 'like', "%$search%")
            ->orWhere('last_name', 'like', "%$search%")
            ->orWhere('email_1', 'like', "%$search%")
            ->get();

        $result = collect($contacts)->reduce(function($result, $contacts) {

            $contact['label'] = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . ' (' . array_get($contacts, 'email_1') . ')';
            $contact['value'] = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . ' (' . array_get($contacts, 'email_1') . ')';
            $contact['data'] = Crypt::encrypt(array_get($contacts, 'id'));
            $contact['id'] = array_get($contacts, 'id');

            $paymentOptions = [];
            foreach ($contacts->paymentOptions as $option) {
                $item = [];
                if (array_get($option, 'category') === 'cc') {
                    $item[array_get($option, 'id')] = array_get($option, 'card_type') . ' [' . array_get($option, 'card_number') . ']';
                } else if (array_get($option, 'category') === 'bank') {
                    $item[array_get($option, 'id')] = array_get($option, 'category') . ' [' . array_get($option, 'bank_account') . ']';
                }

                array_push($paymentOptions, $item);
            }
            $contact['paymentOptions'] = $paymentOptions;

            array_push($result, $contact);
            return $result;
        }, []);

        return $result;
    }

    public function searchInUsers(Request $request) 
    {
        $search = array_get($request, 'search');
        $searchParam = '%' . $search . '%';
        $searchRole = array_get($request, 'searchRole');

        // Optimized search query over all 3 columns
        $users = User::whereRaw("CONCAT(IFNULL(name,''), ' ', IFNULL(last_name,''), ' ', IFNULL(email,'')) like ?", [$searchParam]);
        
        if ($searchRole) {
            $users->whereHas('roles', function ($query) use ($searchRole) {
                $query->where('id', $searchRole);
            });
        }
        
        $users = $users->get();

        $result = collect($users)->reduce(function ($result, $users) {
            $user['label'] = array_get($users, 'name') . ' ' . array_get($users, 'last_name') . ' (' . array_get($users, 'email') . ')';
            $user['value'] = array_get($users, 'name') . ' ' . array_get($users, 'last_name') . ' (' . array_get($users, 'email') . ')';
            $user['data'] = Crypt::encrypt(array_get($users, 'id'));
            $user['id'] = array_get($users, 'id');
            $user['name'] = array_get($users, 'name');
            $user['last_name'] = array_get($users, 'last_name');
            $user['email'] = array_get($users, 'email');
            $user['role_id'] = array_get($users, 'roles.0.id');
            $user['role_name'] = array_get($users, 'roles.0.display_name');
            $user['can_update'] = auth()->user()->can('user-update');
            array_push($result, $user);
            return $result;
        }, []);

        return $result;
    }

    private function searchInContacts(Request $request) {
        $search = array_get($request, 'search');
        $searchParam = '%' . $search . '%';
//        $contacts = Contact::where('first_name', 'like', "%$search%")
//            ->orWhere('last_name', 'like', "%$search%")
//            ->orWhere('email_1', 'like', "%$search%")
//            ->get();

        // Optimized search query over all 3 columns
        $contacts = Contact::whereRaw("CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''), ' ', IFNULL(email_1,''), IFNULL(phone_numbers_only,'')) like ?", [$searchParam])
        // also search company name
        ->orWhereRaw("company like ?", [$searchParam])
        ->select(\DB::raw("contacts.*, IF(contacts.company IS NOT NULL AND contacts.company like '$searchParam', 1, 0) matches_company"))
        ->orderByRaw("company like ?", [$searchParam])
        ->get();
        // dd($contacts);
        
        $result = collect($contacts)->reduce(function($result, $contacts) {
            if (array_get($contacts, 'matches_company')) {
                if (array_get($contacts, 'type') === 'person') {
                    $label = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . '   from   ' . array_get($contacts, 'company');
                } else {
                    $label = array_get($contacts, 'company').' ('.array_get($contacts, 'email_1').')';
                }
            } else {
                $label = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . ' (' . array_get($contacts, 'email_1') . ')';
            }
            
            $contact['label'] = $label;
            $contact['value'] = $label;
            $contact['data'] = Crypt::encrypt(array_get($contacts, 'id'));
            $contact['id'] = array_get($contacts, 'id');
            array_push($result, $contact);
            return $result;
        }, []);
        return $result;
    }

    private function searchInTags(Request $request) {
        $tags = explode('-', array_get($request, 'tags'));
        $search = array_get($request, 'query');

        $contacts = Contact::select('contacts.id', 'contacts.first_name', 'contacts.last_name', 'contacts.email_1', 'contact_tag.tag_id')
            ->join('contact_tag', function($join) use ($tags) {
                $join->on('contact_tag.contact_id', '=', 'contacts.id')->whereIn('contact_tag.tag_id', $tags);
            })
            ->where('first_name', 'like', "%$search%")
            ->orWhere('last_name', 'like', "%$search%")
            ->groupBy('contacts.id', 'contacts.first_name', 'contacts.last_name', 'contacts.email_1', 'contact_tag.tag_id')
            ->get();

        $result = collect($contacts)->reduce(function($result, $contacts) {
            $contact['value'] = array_get($contacts, 'first_name') . ' ' . array_get($contacts, 'last_name') . ' (' . array_get($contacts, 'email_1') . ')';
            $contact['data'] = Crypt::encrypt(array_get($contacts, 'id'));
            $contact['id'] = array_get($contacts, 'id');
            array_push($result, $contact);
            return $result;
        }, []);

        return $result;
    }

    private function searchInForms(Request $request) {
        $f = explode('-', array_get($request, 'forms'));
        $forms = Form::with('entries.contact')->whereIn('id', $f)->get();
        $result = collect($forms)->reduce(function($result, $form) {
            foreach ($form->entries()->onlyTrashed()->whereNotNull('contact_id')->get() as $entry) {
                $contact['value'] = array_get($entry->contact, 'first_name') . ' ' . array_get($entry->contact, 'last_name') . ' (' . array_get($entry->contact, 'email_1') . ') ' . __('in ') . array_get($form, 'name') . ' tag';
                $contact['data'] = Crypt::encrypt(array_get($entry->contact, 'id'));
                $contact['id'] = array_get($entry->contact, 'id');
                array_push($result, $contact);
            }

            return $result;
        }, []);

        return $result;
    }

    public function mobileContactsSearch(Request $request) {
        $result = [];

        if ($request->has('tags')) {
            $result = $this->searchInTags($request);
        } else if ($request->has('forms')) {
            $result = $this->searchInForms($request);
        } else {
            $result = $this->searchInContacts($request);
        }

        $data = ['suggestions' => $result];
        return response()->json($data);
    }

    public function countriesAutocomplete(Request $request) {
        $countries = $this->getCountriesAutocomplete();
        return response()->json($countries);
    }

    public function eventGetData($id) {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        $event->template;
        $event->template->calendar;
        $event->template->ticketOptions;

        $address = array_get($event, 'template.addressInstance.0');
        $fullAddress = implode('<br/>', [
            array_get($address, 'mailing_address_1'),
            array_get($address, 'city'),
            array_get($address, 'region'),
            array_get($address, 'countries.name')
        ]);

        if (array_get($event, 'template.is_all_day')) {
            $date = Carbon::parse(array_get($event, 'start_date'))->toFormattedDateString();
        } else {
            $date = implode(' - ', [
                displayLocalDateTime(array_get($event, 'start_date'), array_get($event, 'template.timezone'))->toDayDateTimeString(),
                displayLocalDateTime(array_get($event, 'end_date'), array_get($event, 'template.timezone'))->toDayDateTimeString()
            ]);
        }

        $data = [
            'event' => $event,
            'date' => $date,
            'address' => $fullAddress,
            'uid' => Crypt::encrypt(array_get($event, 'id'))
        ];
        $permissions = [
            'delete' => Gate::forUser(\auth()->user())->allows('delete', $event),
            'show' => Gate::forUser(\auth()->user())->allows('show', $event),
            'update' => Gate::forUser(\auth()->user())->allows('update', $event),
        ];
        $data['permission'] = $permissions;
        return response()->json($data);
    }

    /**
     * Gets an array of tags available to current tenant with folder information
     * TODO consider moving to new Ajax\TagController
     * @param  Request $request 
     * @return [type]           A Json response 
     */
    public function getTags(Request $request) {
        $tags = Tag::with('folder')->orderBy('tags.folder_id', 'tags.name')->get()->toArray();

        return response()->json($tags);
    }

    /**
     * Stores a new tag provided that specified 'new' tag does not yet exist
     * @param  Request $request Request containing a tag parameter that is an object containing values for a new category
     * @return [null|Tag]           A json response that contains the resulting tag (with id) if succesful, or false otherwise
     */
    public function storeTag(Request $request) {
        $alltagsfolder = 1;
        $vars = $request->all();
        $folder = Folder::find($alltagsfolder);
        $tag = Tag::findOrCreate(array_get($vars, 'tag'), $folder);

        if(is_null($tag)) return response()->json(false);
        if (array_get($request,'includeFolder')) $tag->load('folder');

        return response()->json($tag);
    }
    
    
    /**
     * Appears to search for contacts based on tags or forms and return an array
     *  objects with the following attributes
     *    value: name, email and tag/form info
     *    id: contact id
     *    data: ???
     *
     * TODO consider renaming this method and related routes
     * @param  Request $request A request that contains either 'tags' or 'forms' parameters containing an array of tag or form ids
     * @return [type]           A Json response
     */
    public function tagsGetData(Request $request) {
        //return response()->json($request->all());
        if (!$request->has('tags') && !$request->has('forms')) {
            return response()->json([]);
        }

        if ($request->has('tags')) {
            $result = $this->tags($request);
        } else if ($request->has('forms')) {
            $result = $this->forms($request);
        }

        return response()->json($result);
    }

    /**
     * Searches for contacts based on tags and return an array
     *  objects with the following attributes
     *    value: name, email, and name of tag contact was tagged with
     *    id: contact id
     *    data: ???
     *
     * TODO move to Ajax/TagsController 
     * @param  Request $request A request containing a 'tags' parameter (see calling methodd)
     * @return [array]           An array of objects
     */
    private function tags(Request $request) {
        $t = array_get($request, 'tags');
        if (!$request->ajax()) {
            $t = json_decode(array_get($request, 'tags'));
        }

        $tags = Tag::with('contacts')->whereIn('id', $t)->get();
        $result = collect($tags)->reduce(function($result, $tag) {
            foreach ($tag->contacts as $c) {
                $contact['value'] = array_get($c, 'first_name') . ' ' . array_get($c, 'last_name') . ' (' . array_get($c, 'email_1') . ') ' . __('in ') . array_get($tag, 'name') . ' tag';
                $contact['data'] = Crypt::encrypt(array_get($c, 'id'));
                $contact['id'] = array_get($c, 'id');
                array_push($result, $contact);
            }

            return $result;
        }, []);

        return $result;
    }

    private function forms(Request $request) {
        $f = array_get($request, 'forms');
        if (!$request->ajax()) {
            $f = json_decode(array_get($request, 'forms'));
        }

        $forms = Form::with('entries.contact')->whereIn('id', $f)->get();
        $result = collect($forms)->reduce(function($result, $form) {
            foreach ($form->entries()->onlyTrashed()->whereNotNull('contact_id')->get() as $entry) {
                $contact['value'] = array_get($entry->contact, 'first_name') . ' ' . array_get($entry->contact, 'last_name') . ' (' . array_get($entry->contact, 'email_1') . ') ' . __('in ') . array_get($form, 'name') . ' tag';
                $contact['data'] = Crypt::encrypt(array_get($entry->contact, 'id'));
                $contact['id'] = array_get($entry->contact, 'id');
                array_push($result, $contact);
            }

            return $result;
        }, []);

        return $result;
    }

    public function campaignGetPurpose(Request $request) {
        $campaign = \App\Models\Campaign::findOrFail(array_get($request, 'id'));
        return response()->json($campaign);
    }

    public function oauthShowToken($id, Request $request) {
        $api = TenantToken::where('token_id', $id)->first();
        if ($api) {
            return response()->json(array_get($api, 'token'));
        }
        return response()->json(null);
    }

    public function getChartFromCampaign(Request $request) {
        $campaign = Campaign::find(array_get($request, 'campaign_id'));
        if (is_null($campaign)) {
            return response()->json(false);
        }

        return response()->json($campaign->purpose);
    }

    /**
     * see https://app.asana.com/0/1117069745037349/1181874597981603/f
     */
    public function setTimezone(Request $request) {
        $timezone_offset_seconds = array_get($request, 'offset_seconds');  
        
        $timezone = timezone_name_from_abbr("", $timezone_offset_seconds, 0);
        
        if (! $timezone) {
            MissionPillarsLog::log([
                'event' => 'setTimezone',
                'message' => "timezone_name_from_abbr('', $timezone_offset_seconds, false) failed",
            ]);
        } else {
            $request->session()->put('timezone', $timezone);
        }

        return response()->json(compact('timezone'));
    }

    public function contactsTimeline($id, Request $request) {
        $contact = Contact::findOrFail($id);
        $page = array_get($request, 'page', 1);
        $timeline = $contact->timeline($page);
        
        $phoneNumbers = auth()->user()->contact->SMSPhoneNumbers->pluck('phone_number');
        $hasPhoneNumber = false;
        if ($phoneNumbers->count()) {
            $hasPhoneNumber = true;
        }
        
        $data = [
            'contact' => $contact,
            'timeline' => $timeline,
            'has_phone_number' => $hasPhoneNumber,
            'phoneNumbers' => $phoneNumbers
        ];
        
        $content = view('people.contacts.includes.timeline.index', $data)->render();
        return $content;
    }

    public function ticketsGetTimeLeft(Request $request){
        //check if there is a ticket record that needt to be paid
        $time_left = TicketsTemporaryHold::getTimeLeft($request);
        return response()->json($time_left);
    }

    
    /** Log Error **/
    
    /**
     * handles ajax.errors.store route
     * Logs an error in the database
     * @param  Request $request The POST data can optionaly contain the following: event, message, url, request, response
     * @return [type]         A JSON response of the DB log object
     */
    public function logError(Request $request)
    {
        $logentry = [
            'event' => 'Ajax Error',
            'caller_function' => implode('.',[get_class($this).__FUNCTION__]),
            'url' => url()->current(),
            'data' => json_encode([
                'user_id'=>$request->user()->id,
                'tenant_id'=>$request->user()->tenant_id,
            ]),
        ];
        foreach(['event','message','url','request','response'] as $logproperty) {
            if ($request->has($logproperty)) {
                $value = $request->get($logproperty);
                $logentry[$logproperty] = json_encode( $value );
            }
        }
        $result = MPLog::create($logentry);
        return response()->json($result);
    }
    
    public function familiesAutocomplete(Request $request) 
    {
        $result = $this->searchInFamilies($request);
        return response()->json($result);
    }
    
    private function searchInFamilies(Request $request) 
    {
        $search = array_get($request, 'search');
        $families = Family::where('name', 'like', "%$search%")->orderBy('name')->get();

        return collect($families)->reduce(function($result, $f) {
            $family['label'] = array_get($f, 'name');
            $family['value'] = array_get($f, 'name');
            $family['data'] = Crypt::encrypt(array_get($f, 'id'));
            $family['id'] = array_get($f, 'id');
            array_push($result, $family);
            return $result;
        }, []);
    }
    
    public function viewEmail(Request $request)
    {
        $id = array_get($request, 'id');
        
        $email = EmailSent::findOrFail($id);
        $communicationContent = array_get($email, 'communicationContent');
        $emailData = $this->getQueuedEmailsQuery()->where('email_sent.id', $id)->firstOrFail();
        array_set($emailData, 'subject', array_get($communicationContent, 'subject'));
        array_set($emailData, 'content', array_get($communicationContent, 'content'));
        array_set($emailData, 'email_editor_type', array_get($communicationContent, 'editor_type'));
        
        $data = $this->prepareEmailData($emailData);

        if (array_get($emailData, 'email_editor_type', 'tiny') === 'topol') {
            $html = view('emails.send.general-topol', $data)->render();
        } else {
            $html = view('emails.send.general', $data)->render();
        }
        
        return response()->json(['success' => true, 'subject' => array_get($communicationContent, 'subject'), 'content' => $html]);
    }
    
    public function groupsAutocomplete(Request $request) 
    {
        $result = $this->searchInGroups($request);
        return response()->json($result);
    }
    
    private function searchInGroups(Request $request) 
    {
        $search = array_get($request, 'search');
        $groups = Group::where('name', 'like', "%$search%")->orderBy('name')->get();

        return collect($groups)->reduce(function($result, $g) {
            $group['label'] = array_get($g, 'name');
            $group['value'] = array_get($g, 'name');
            $group['data'] = Crypt::encrypt(array_get($g, 'id'));
            $group['id'] = array_get($g, 'id');
            array_push($result, $group);
            return $result;
        }, []);
    }
    
    public function transactionsAutocomplete(Request $request)
    {
        $result = $this->searchInTransactions($request);
        return response()->json($result);
    }
    
    private function searchInTransactions(Request $request) 
    {
        $search = array_get($request, 'search');
        $transactions = Transaction::whereHas('contact', function ($query) use ($search) {
            $query->where('first_name', 'like', "%$search%")
            ->orWhere('last_name', 'like', "%$search%")
            ->orWhere('email_1', 'like', "%$search%");
        })->with([
            'contact', 
            'template', 
            'splits', 
            'splits.purpose', 
            'splits.campaign', 
            'splits.transaction', 
            'splits.transaction.contact', 
            'splits.transaction.template', 
            'splits.transaction.paymentOption', 
            'splits.registry:register_id', 
            'splits.transaction.softCredits.splits',
            'splits.transaction.softCredits.contact',
            'splits.transaction.documents',
            'splits.transaction.splits.campaign',
            'splits.transaction.splits.purpose',
            'splits.transaction.splits.tags',
        ])->orderBy('transaction_initiated_at', 'desc')->take(20)->get();

        return collect($transactions)->reduce(function($result, $t) {
            $transactionTime = displayLocalDateTime(array_get($t, 'transaction_initiated_at'))->format('m/d/Y');
            $transaction['label'] = '$'.array_get($t, 'template.amount').' '.array_get($t, 'contact.full_name').' '.$transactionTime;
            $transaction['value'] = '$'.array_get($t, 'template.amount').' '.array_get($t, 'contact.full_name').' '.$transactionTime;
            $transaction['data'] = Crypt::encrypt(array_get($t, 'id'));
            $transaction['id'] = array_get($t, 'id');
            $transaction['split'] = array_get($t, 'splits.0');
            array_push($result, $transaction);
            return $result;
        }, []);
    }
}
