<?php

namespace App\Http\Controllers;

use App\Classes\Email\EmailQueue;
use App\Classes\Shared\Transactions\SharedTransactions;
use App\Classes\Shared\Transactions\SharedRecurringTransactions;
use App\Classes\XLSFiles\XLSFiles;
use App\Constants;
use App\Http\Requests\Contacts\UpdateContact;
use App\Http\Requests\Contacts\UpdateContactFamily;
use App\Http\Requests\Contacts\StoreContact;
use App\Http\Requests\Contacts\RelativeRelationship;
use App\Models\Address;
use App\Models\CalendarEventTemplateSplit;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldSection;
use App\Models\CustomFieldValue;
use App\Models\Email;
use App\Models\Folder;
use App\Models\FormEntry;
use App\Models\Group;
use App\Models\SMSContent;
use App\Models\SMSPhoneNumber;
use App\Models\SMSSent;
use App\Models\StatementTemplate;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplateSplit;
use App\Models\User;
use App\Traits\CountriesTrait;
use App\Traits\TagsTrait;
use App\Traits\FamilyTrait;

use Carbon\Carbon;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use Intervention\Image\ImageManagerStatic as Image;

class ContactsController extends Controller {

    use TagsTrait,
        FamilyTrait,
        CountriesTrait;

    const PERMISSION = 'crm-contacts';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            // NOTE Making an exception to the normal subscription redirect if is accessing own route 
            if(( !auth()->user()->contact || $request->route('contact') != auth()->user()->contact->id ) && !auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO);
            }
            return $next($request);
        });
    }

    private function sort($sort) {
        switch ($sort) {
            case 'lastname':
                $field = 'last_name';
                break;
            case 'email':
                $field = 'email_1';
                break;
            case 'firstname':
            default :
                $field = 'first_name';
                break;
        }
        return $field;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        /**
         * TODO remove old contacts page and code
         * Using only advanced search from now on
         */
        return redirect()->route('search.contacts');
        
        $this->authorize('viewAll',Contact::class);
        $sort = 'firstname';
        $order = 'asc';
        if ($request->has('sort')) $sort = array_get($request, 'sort');
        $field = $this->sort($sort);
        
        if ($request->has('order')) $order = array_get($request, 'order');
        $contacts = Contact::orderBy($field, $order);
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';

        $data = [
            'contacts' => $contacts->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $contacts->count()
        ];

        return view('people.contacts.index')->with($data);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $this->authorize('create',Contact::class);
        $data = [
            'contact' => null,
            'countries' => $this->getCountries(),
            'customFields' => null,
            'customFieldSections' => null,
            'customFieldsImported' => null,
            'tab' => 'profile',
            'entry_id' => array_get($request, 'id'),
            'imagePath' => null,
            'showRemoveButton' => false,
            'familyPositions' => $this->getFamilyPositions()
        ];
        return view('people.contacts.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContact $request) {
        $message = __('Contact successfully saved');
        $contact = mapModel(new Contact(), $request->all());

        if (array_get($contact, 'type') === 'organization') {
            array_set($contact, 'company', array_get($request, 'organization_name'));
            array_set($contact, 'family_id', null);
            array_set($contact, 'family_position', null);
        }
        
        if (array_get($request, 'family_id') === '0') {
            array_set($contact, 'family_id', null);
            array_set($contact, 'family_position', null);
        }
        
        if (!auth()->user()->tenant->contacts()->save($contact)) abort(500);
        $contact->refresh();
        
        if (!is_null(array_get($request, 'mailing_address_1')) || !is_null(array_get($request, 'mailing_address_2'))) {
            $address = mapModel(new Address(), $request->all());
            array_set($address, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
            array_set($address, 'relation_id', array_get($contact, 'id'));
            array_set($address, 'relation_type', Contact::class);
            $address->save();
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $this->storeProfileImage($contact, $request);
        }
        
        if ($request->has('addToGroups') && array_get($request, 'addToGroups')) {
            $uuids = explode(',', array_get($request, 'addToGroups'));
            $groups = Group::whereIn('uuid', $uuids)->get()->pluck('id')->toArray();
            
            if ($groups) {
                $contact->groups()->attach($groups);
            }
        }
        
        if( !is_null(array_get($request, 'entry_id')) ){
            $entry = FormEntry::findOrFail(array_get($request, 'entry_id'));
            DB::table('contact_entry')->insert([
                [
                    'form_entry_id' => array_get($entry, 'id'),
                    'contact_id' => array_get($contact, 'id'),
                    'relationship' => array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT')
                ]
            ]);

            return redirect(route('entries.show', ['id' => array_get($entry, 'id')]))->with('message', $message);
        }
        
        if ($request->has('mainContactForm')) {
            if (auth()->user()->cannot('show', $contact)) {
                return response()->json(['id' => array_get($contact, 'id'),'message' => $message, 'redirect' => route('contacts.index')]);
            } else {
                return response()->json(['id' => array_get($contact, 'id'),'message' => $message, 'redirect' => route('contacts.show', ['id' => array_get($contact, 'id')])]);
            }
        }
        
        if (auth()->user()->cannot('update', $contact)) {
            if (auth()->user()->cannot('show', $contact)) {
                return redirect()->route('contacts.index')->with('message', $message);
            } else {
                return redirect()->route('contacts.show', ['id' => array_get($contact, 'id')])->with('message', $message);
            }
        }

        if ($request->ajax()){
            return $contact;
        }

        return redirect()->route('contacts.edit', ['id' => array_get($contact, 'id')])->with('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        if (!auth()->user()->can('contact-view')) {
            abort(403);
        }
        
        $contact = Contact::withTrashed()->findOrFail($id);
        
        if ($contact->trashed()) {
            return redirect()->route('contacts.restore.show', $contact);
        }
        
        $this->authorize('show',$contact);
        $completedtransactions = $contact->transactionSplits()->completed()->get();

        $total_amount_last_year = TransactionSplit::whereHas('transaction', function($q) use ($contact){
            $q->whereBetween('transaction_initiated_at', [
                Carbon::now()->subYear()->startOfYear(),
                Carbon::now()->subYear()->endOfYear()
            ])->where('contact_id', array_get($contact, 'id'))
            ->where('status', 'complete');
        })->sum('amount');

        $total_amount_this_year = TransactionSplit::whereHas('transaction', function($q) use($contact){
            $q->whereBetween('transaction_initiated_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])->where('contact_id', array_get($contact, 'id'))
            ->where('status', 'complete');
        })->sum('amount');

        $last_gift = Transaction::where([
            ['status', '=', 'complete'],
            ['contact_id', '=', array_get($contact, 'id')]
        ])->orderBy('transaction_initiated_at', 'desc')->first();
                
        $customFields = $contact->customFieldValues()->with('customField')->whereHas('customField', function($query) {
            $query->notImported();
        })->whereNotNull('value')->where('value', '<>', '')->get()->sort(function ($a, $b) {
            return $a->customField->custom_field_section_id === $b->customField->custom_field_section_id ? $a->customField->sort <=> $b->customField->sort : $a->customField->custom_field_section_id <=> $b->customField->custom_field_section_id;
        });
        
        $data = [
            'contact' => $contact,
            'tasks' => $contact->tasks,
            'completedtransactions' => $completedtransactions,
            'total_amount_last_year' => $total_amount_last_year,
            'total_amount_this_year' => $total_amount_this_year,
            'last_gift' => $last_gift,
            'familyPositions' => $this->getFamilyPositions(),
            'relationships' => array_get($contact, 'type') === 'organization' ? $this->getOrganizationRelationships() : $this->getFamilyRelationships(),
            'customFields' => $customFields
        ];
        
        return view('people.contacts.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        if ((int) $id === 0) {
            auth()->user()->createContact();
        }
        try {
            $contact = Contact::withTrashed()->findOrFail(Crypt::decrypt($id));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            $contact = Contact::withTrashed()->find($id);
        }
        if (!$contact) {
            return redirect()->route('contacts.index')->with('error', __('Contact not found'));
        }
        
        if ($contact->trashed()) {
            return redirect()->route('contacts.restore.show', $contact);
        }
        
        $this->authorize('update',$contact);
        $customFieldsImported = CustomFieldValue::whereHas('customField', function($query) {
            $query->imported();
        })->where([
            ['relation_id', '=', array_get($contact, 'id')],
            ['relation_type', '=', Contact::class]
        ])->get();

        $customFields = CustomField::notImported()->ordered()->get();
        
        $customFields->map(function ($field) use ($contact) {
            $value = CustomFieldValue::where([
                ['relation_id', '=', array_get($contact, 'id')],
                ['relation_type', '=', Contact::class],
                ['custom_field_id', '=', array_get($field, 'id')]
            ])->first();
            
            if (array_get($field, 'type') === 'multiselect') {
                $field->value = explode(',', array_get($value, 'value'));
            } else {
                $field->value = array_get($value, 'value');
            }
        });
        
        $customFieldSections = CustomFieldSection::whereHas('customFields')->ordered()->get();
        
        $data = [
            'contact' => $contact,
            'uid' => Crypt::encrypt($id),
            'customFieldsImported' => $customFieldsImported,
            'countries' => $this->getCountries(),
            'imagePath' => (array_get($contact, 'profile_image') && $contact->profile_image_src !== asset('img/contact_no_profile_image.png')) ? $contact->profile_image_src : null,
            'showRemoveButton' => (array_get($contact, 'profile_image') && $contact->profile_image_src !== asset('img/contact_no_profile_image.png')) ? true : false,
            'familyPositions' => $this->getFamilyPositions(),
            'customFields' => $customFields,
            'customFieldSections' => $customFieldSections
        ];

        return view('people.contacts.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateContact $request, $id) {
        $contact = $request->contact_;
        $message = __('Contact successfully updated');
        array_set($contact, 'send_paper_contribution_statement', false);
        array_set($contact, 'confirmed_no_allergies', false);
        array_set($contact, 'active', false);
        array_set($contact, 'baptized', false);
        array_set($contact, 'is_private', false);
        mapModel($contact, $request->all());
        
        if (array_get($contact, 'type') === 'organization') {
            array_set($contact, 'company', array_get($request, 'organization_name'));
            array_set($contact, 'family_id', null);
            array_set($contact, 'family_position', null);
        }
        
        if ($contact->update()) {
            if ($request->has('removeCoverImage')) {
                if (env('AWS_ENABLED')) {
                    Storage::disk('s3')->delete($contact->profile_image);
                } else {
                    checkAndDeleteFile(storage_path('app/public/contacts/' . $contact->profile_image));
                }
                
                array_set($contact, 'profile_image', null);
                $contact->update();
            }
            
            if ($request->hasFile('image') && $request->file('image')->isValid() && !$request->has('removeCoverImage')) {
                $this->storeProfileImage($contact, $request);
            }
            
            if ($request->has('customFieldsData')) {
                $this->storeCustomFields($contact, $request);
            }
            
            $tab = array_get($request, 'tab', 'profile');
            
            if ($request->has('mainContactForm')) {
                if (auth()->user()->can('contact-view')) {
                    return response()->json(['id' => array_get($contact, 'id'),'message' => $message, 'redirect' => route('contacts.show', ['id' => array_get($contact, 'id')])]);
                } elseif (auth()->user()->can('edit-profile')) {
                    return response()->json(['id' => array_get($contact, 'id'),'message' => $message, 'redirect' => route('contacts.edit-profile')]);
                }
            }
            
            return redirect()->route('contacts.edit', ['id' => $contact->id, 'tab' => $tab])->with('message', $message);
        }
        
        abort(500);
    }

    public function updateAbout(Request $request, $id) {
        $message = __('Contact successfully updated');
        $contact = Contact::find($id);
        mapModel($contact, $request->all());
        if ($contact->update()) {
            return redirect()->back();
        }
        abort(500);
    }
    
    public function updateChildCheckinNote(Request $request, $id) 
    {
        $message = __('Contact successfully updated');
        $contact = Contact::find($id);
        mapModel($contact, $request->all());
        if ($contact->update()) {
            return redirect()->back();
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request) {
        $contact = Contact::findOrFail($id);
        $this->authorize('delete',$contact);
        $message = __('Contact successfully deleted');
        Contact::destroy($id);
        $contact->updated_by = auth()->user()->id;
        $contact->updated_by_session_id = Session::getId();
        $contact->update();
        return redirect()->route('contacts.index')->with('message', $message);
    }

    public function tags($id, Request $request) {
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));

        $folder = array_get($root, 'id');
        if ($request->has('folder')) {
            $folder = array_get($request, 'folder');
        }

        $contact = Contact::find($id);
        $tagsArray = collect($contact->tags)->map(function($tag) {
            return $tag['id'];
        });

        $data = $this->getDataTree($root, $folder);
        $data['contact'] = $contact;
        $data['tagsArray'] = $tagsArray->toArray();

        if (is_null($data['root'])) {
            abort(404);
        }
        return view('people.contacts.tags')->with($data);
    }

    public function transactions($id, Request $request) {
        if (!auth()->user()->can('transaction-view')) {
            abort(403);
        }
        
        $contact = Contact::findOrFail($id);

        $order = array_get($request, 'order', 'desc');
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        $sort = array_get($request, 'sort', 'id');

        $statement = null;
        if (array_has($request, 'st')) {
            $statement = \App\Models\StatementTracking::findOrFail(array_get($request, 'st', 0));
        }

        $transactions = SharedTransactions::all($sort, $order, $contact, $statement);

        $total = $transactions->get();

        $templates = StatementTemplate::all();
        
        // TODO Consider adjusting permissions if the current contact is auth()->user()->contact
        $transaction_permissions = array_get(auth()->user()->ability([],[
            'transaction-create',
            'transaction-view',
            'transaction-update',
            'transaction-delete', 
        ],['return_type'=>'array']),'permissions');
        
        $data = [
            'contact' => $contact,
            'splits' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count(),
            'uuid' => Uuid::uuid4(),
            'start' => Carbon::now()->startOfYear()->toDateString(),
            'end' => Carbon::now()->endOfDay()->toDateString(),
            'templates' => $templates,
            'print_for' => ['contact' => 'Contact'],
            'statement' => null,
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts'),
            'transaction_permissions' => $transaction_permissions,
        ];
        
        return view('people.contacts.transactions')->with($data);
    }

    public function recurringTransactions($id, Request $request) {
        $contact = Contact::findOrFail($id);
        $order = array_get($request, 'order', 'desc');
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        $sort = array_get($request, 'sort', 'id');
        $transactions = SharedRecurringTransactions::all($sort, $order, $contact);
        $total = $transactions->get();

        $data = [
            'contact' => $contact,
            'splits' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count()
        ];
        return view('people.contacts.recurring_transactions')->with($data);
    }

    public function recurringTransactions1($id) {
        $contact = Contact::findOrFail($id);

        $transactions = Transaction::whereHas('template', function($q) {
                $q->where([
                    ['is_recurring', '=', true],
                    ['status', '!=', 'stub']
                ]);
            })
            ->where('id', array_get($contact, 'id'))
            ->orderBy('id', 'desc')
            ->get();

        $recurring = collect($transactions)->map(function($transaction) {
            return $transaction->template;
        });

        $data = [
            'contact' => $contact,
            'recurring' => $recurring
        ];

        return view('people.contacts.recurring_transactions')->with($data);
    }

    /**
     * Tag a contact
     * @param Request $request
     * @return type
     */
    public function tagContact(Request $request) {
        try {
            $id = Crypt::decrypt(array_get($request, 'cid'));
            $contact = Contact::find($id);
            $tags = array_get($request, 'tags', []);

            $contact->tags()->sync($tags);

            return redirect()->route('contacts.tags', ['id' => $id, 'folder' => array_get($request, 'folder')])->with(['message' => 'Tags were updated successfully']);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect('cheating');
        }
        abort(500);
    }

    public function groups($id, Request $request) {
        $root = Folder::find(array_get(Constants::GROUPS_SYSTEM, 'FOLDERS.ALL_GROUPS'));

        $folder = array_get($root, 'id');
        if ($request->has('folder')) {
            $folder = array_get($request, 'folder');
        }

        $contact = Contact::find($id);
        $groupsArray = collect($contact->groups)->map(function($group) {
            return $group['name'];
        });

        $leadsArray = collect($contact->leads)->map(function($lead) {
            return $lead['name'];
        });

        $data = $this->getDataTree($root, $folder, 'GROUPS');
        $data['contact'] = $contact;
        $data['groupsArray'] = $groupsArray->toArray();
        $data['leadsArray'] = $leadsArray->toArray();

        if (is_null($data['root'])) {
            abort(404);
        }

        $folderDropdown = collect(Folder::where('type', 'GROUPS')->orderBy('name')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);

        return view('people.contacts.groups')->with($data);
    }

    public function groupContact(Request $request) {
        try {
            $id = Crypt::decrypt(array_get($request, 'cid'));
            $contact = Contact::find($id);

            $detach = array_get($request, 'detach');
            $this->detach($detach, $contact);

            $groups = array_get($request, 'groups');
            $leads = array_get($request, 'leads');
            $this->sync($groups, $leads, $contact);

            return redirect()->route('contacts.groups', ['id' => $id, 'folder' => array_get($request, 'folder')]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect('cheating');
        }
        abort(500);
    }

    /**
     * sync only groups andleads that were checked in view
     * @param type $groups
     * @param type $leads
     * @param type $contact
     */
    private function sync($groups, $leads, $contact) {
        $contact->groups()->sync($groups, false);
//        $contact->leads()->sync($leads, false);
//
//        //finally add the corresponding tags
//        $groupModels = Group::find($groups);
//        $tags = collect($groupModels)->map(function($group) {
//            return $group['map_tag_id'];
//        });
//
//        if (count($contact->groups) > 0) {
//            $tags->push(array_get(Constants::GROUPS_SYSTEM, 'GROUPS.GROUP_MEMBER'));
//        }
//        if (count($contact->leads) > 0) {
//            $tags->push(array_get(Constants::GROUPS_SYSTEM, 'GROUPS.GROUP_LEADER'));
//        }
//
//        $contact->tags()->sync($tags, false);
    }

    /**
     * detach all groups and leads from view
     * @param type $detach
     * @param type $contact
     */
    private function detach($detach, $contact) {
        $contact->groups()->detach($detach);
//        $contact->leads()->detach($detach);
//        $groupModels = Group::find($detach);
//        $untagged = collect($groupModels)->map(function($group) {
//            return $group['map_tag_id'];
//        });
//        $untagged->push(array_get(Constants::GROUPS_SYSTEM, 'GROUPS.GROUP_MEMBER'));
//        $untagged->push(array_get(Constants::GROUPS_SYSTEM, 'GROUPS.GROUP_LEADER'));
//        $contact->tags()->detach($untagged);
    }

    public function forms($id, Request $request) {
        $contact = Contact::findOrFail($id);
        
        $data = [
            'contact' => $contact,
            'total' => count(array_get($contact, 'formEntries', [])),
            'entries' => $contact->formEntries()->groupBy('contact_entry.form_entry_id')->orderBy('id', 'desc')->paginate()
        ];
        return view('people.contacts.forms')->with($data);
    }

    public function form($id, $entryId, Request $request) {
        $contact = Contact::findOrFail($id);
        $entry = $contact->formEntries()->where([
            ['contact_entry.form_entry_id', '=', $entryId]
        ])->first();
        
        $form = array_get($entry, 'form');
        $json = array_get($entry, 'json');
        $fields = json_decode($json, true);
        
        if (array_has($fields, 'payment')) {
            $flat = array_flatten(array_get($fields, 'payment', []));
            array_set($fields, 'payment', $flat);
            $json = json_encode($fields);
        }
        
        $contact = Contact::whereHas('formEntries', function($q) use($entry){
            $q->where('contact_entry.relationship', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT'))
                ->where('contact_entry.form_entry_id', array_get($entry, 'id'));
        })->first();

        $payer = Contact::whereHas('formEntries', function($q) use($entry){
            $q->where('contact_entry.relationship', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER'))
                ->where('contact_entry.form_entry_id', array_get($entry, 'id'));
        })->first();
        
        $transaction = array_get($entry, 'transaction');
        $split = null;
        if(!is_null($transaction)){
            $split = $transaction->splits->where('type', 'purchase')->first();
        }

        $match = [];
        if(array_has($fields, 'email_1')){
            $match = Contact::where([
                ['email_1', '=', array_get($fields, 'email_1', '@')]
            ])->orWhere(function($q) use($fields){
                $q->where('first_name', array_get($fields, 'first_name'))
                    ->where('last_name', array_get($fields, 'last_name'));
            })->get();
        }

        $data = [
            'entry' => $entry,
            'form' => $form,
            'json' => array_get($entry, 'json', '[]'),
            'split' => $split,
            'fields' => $fields,
            'contact' => $contact,
            'payer' => $payer,
            'match' => $match
        ];
        return view('people.contacts.entry')->with($data);
    }

    public function deleteRelative($id, Request $request) 
    {
        $contact = Contact::findOrFail($id);
        $contact->relatives()->detach(array_get($request, 'relative_id'));
        
        return response()->json(['message' => __('Relation successfully deleted')]);
    }

    public function addRelative($id, RelativeRelationship $request) 
    {
        $contact = Contact::findOrFail($id);
        $relative = Contact::findOrFail(array_get($request, 'relative_id'));
        
        if (array_get($request, 'contact_relationship') === 'Other' && !empty(array_get($request, 'contact_relationship_other'))) {
            $contactRelationship = array_get($request, 'contact_relationship_other');
        } else {
            $contactRelationship = array_get($request, 'contact_relationship');
        }
        
        if (array_get($request, 'relative_relationship') === 'Other' && !empty(array_get($request, 'relative_relationship_other'))) {
            $relativeRelationship = array_get($request, 'relative_relationship_other');
        } else {
            $relativeRelationship = array_get($request, 'relative_relationship');
        }
        
        $sync = [
            array_get($relative, 'id') => [
                'contact_relationship' => $contactRelationship,
                'relative_relationship' => $relativeRelationship
            ]
        ];
        $contact->relatives()->sync($sync, false);
        
        return response()->json(['message' => __('Relation successfully added')]);
    }
    
    public function updateRelative(Request $request)
    {
        $contact = Contact::findOrFail(array_get($request, 'contact_id'));
        $relative = Contact::findOrFail(array_get($request, 'relative_id'));
        
        if (array_get($request, 'contact_relationship') === 'Other' && !empty(array_get($request, 'contact_relationship_other'))) {
            $contactRelationship = array_get($request, 'contact_relationship_other');
        } else {
            $contactRelationship = array_get($request, 'contact_relationship');
        }
        
        if (array_get($request, 'relative_relationship') === 'Other' && !empty(array_get($request, 'relative_relationship_other'))) {
            $relativeRelationship = array_get($request, 'relative_relationship_other');
        } else {
            $relativeRelationship = array_get($request, 'relative_relationship');
        }
        
        $sync = [
            array_get($relative, 'id') => [
                'contact_relationship' => array_get($request, 'relative_up') ? $relativeRelationship : $contactRelationship,
                'relative_relationship' => array_get($request, 'relative_up') ? $contactRelationship : $relativeRelationship
            ]
        ];
        $contact->relatives()->sync($sync, false);
        
        return response()->json(['message' => __('Relation successfully updated')]);
    }

    public function composeEmail($id) {
        $contact = Contact::findOrFail($id);
        $templates = StatementTemplate::all();
        $data = [
            'contact' => $contact,
            'templates' => $templates
        ];

        return view('people.contacts.composer')->with($data);
    }

    public function email($id, Request $request) {
        if (array_get($request, 'action') === 'preview') {
            return $this->sendPreview($id, $request);
        } else {
            return $this->setEmailQueue($id, $request);
        }

        return redirect()->route('contacts.compose', ['id' => $id])->with('error', __('An error occurred trying to send email'));
    }

    public function sendPreview($id, $request) {
        $content = $request->input('content');
        $contact = array_get(auth()->user(), 'contact');

        $args = [
            'from_name' => array_get($request, 'from_name'),
            'from_email' => array_get($request, 'from_email'),
            'subject' => array_get($request, 'subject'),
            'reply_to' => array_get($request, 'reply_to', null),
            'content' => $content,
            'cc_secondary' => array_get($request,'cc_secondary') == 'true' ? 1 : null,
            'model' => auth()->user(),
            'queued_by' => 'contacts.email.preview',
            'include_transactions' => 0
        ];
        EmailQueue::set($contact, $args);
        return $request->ajax() ? response()->json(true) : redirect()->route('contacts.compose', ['id' => $id])->with('message', __('Email preview successfully sent'));
    }

    private function setEmailQueue($id, $request) {
        $content = $request->input('content');
        $contact = Contact::findOrFail($id);
        $args = [
            'from_name' => array_get($request, 'from_name'),
            'from_email' => array_get($request, 'from_email'),
            'reply_to' => array_get($request, 'reply_to'),
            'subject' => array_get($request, 'subject'),
            'content' => $content,
            'model' => $contact,
            'queued_by' => 'contacts.email.send',
            'include_transactions' => $request->include_transactions ? 1 : 0,
            'cc_secondary' => $request->cc_secondary ? 1 : null,
            'transaction_start_date' => $request->include_transactions ? Carbon::parse($request->start_date) : null,
            'transaction_end_date' => $request->include_transactions ? Carbon::parse($request->end_date) : null,
        ];
        if ($request->include_transactions){
            Validator::make($request->all(),[
                'start_date' => 'required',
                'end_date' => 'required',
            ])->validate();
        }

        EmailQueue::set($contact, $args);
        return redirect()->route('contacts.compose', ['id' => $id])->with('message', __('Email successfully sent'));
    }

    /*
      private function setEmailQueue($id, $request) {
      $contact = Contact::findOrFail($id);
      $email = mapModel(new Email(), $request->all());
      array_set($email, 'relation_id', array_get($contact, 'id'));
      array_set($email, 'relation_type', get_class($contact));
      if (auth()->user()->tenant->emails()->save($email)) {
      //$email->sendToContact($id);
      $contact->sendEmail($email);
      return redirect()->route('contacts.compose', ['id' => $id])->with('message', __('Email successfully sent'));
      }
      }
     */

    public function paymentOptions(Request $request) {
        $contact = Contact::find(array_get($request, 'id'));
        if(is_null($contact)){
            return response()->json(['status' => 'null_contact']);
        }
        $options = [];
        $payments = $contact->paymentOptions()->where('category', array_get($request, 'category'))->get();
        if (!is_null($payments)) {
            $options = $payments->reduce(function($carry, $item) {
                $option = ['id' => null, 'option' => null];
                if (array_get($item, 'category') === 'cc') {
                    $option['id'] = array_get($item, 'id');
                    $option['option'] = array_get($item, 'card_type') . ' ****' . array_get($item, 'last_four');
                    $option['card_type'] = array_get($item, 'card_type');
                    $option['last_four'] = array_get($item, 'last_four');
                }

                if (in_array(array_get($item, 'category'), ['ach', 'check'])) {
                    $option['id'] = array_get($item, 'id');
                    $option['option'] = 'Account ****' . array_get($item, 'last_four');
                    $option['card_type'] = 'Account # ****';
                    $option['last_four'] = array_get($item, 'last_four');
                }
                $carry[] = $option;
                return $carry;
            }, []);
        }

        return response()->json($options);
    }

    public function import(Request $request) {
        $this->authorize('import',Contact::class);
        return view('people.contacts.import');
    }

    public function uploadDataSheet(Request $request) 
    {
        ini_set('max_execution_time', 300);
        
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            XLSFiles::import($file);
        }
        return redirect()->route('contacts.index')->with('message', __('File imported successfully'));
    }

    public function about($id, Request $request) {
        $contact = Contact::findOrFail($id);
        $data = [
            'contact' => $contact
        ];
        return view('people.contacts.about')->with($data);
    }

    public function notes($id, Request $request) {
        $contact = Contact::findOrFail($id);
        $data = [
            'contact' => $contact,
            'relation' => $contact,
            'notes' => $contact->notes()->paginate()
        ];

        return view('people.contacts.notes')->with($data);
    }

    public function composeSMS($id, Request $request)
    {
        $contact = Contact::findOrFail($id);
        $sms = $contact->texts;

        $unread = $contact->unreadTexts;
        foreach ($unread as $item) {
            $item->read = 1;
            $item->save();
        }
        
        $phoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
        $phoneNumbersSelect = [];
        
        $hasPhoneNumber = false;
        if ($phoneNumbers->count()) {
            $hasPhoneNumber = true;
            foreach ($phoneNumbers as $phone) {
                $phoneNumbersSelect[array_get($phone, 'id')] = array_get($phone, 'name_and_number');
            }
        }
        
        $page = array_get($request, 'page', 1);
        $perPage = 15;
        $collection = collect($sms);
        $paginated = new LengthAwarePaginator($collection->forPage($page, $perPage), $collection->count(), $perPage, $page, ['path'=>route('contacts.sms', ['id' => array_get($contact, 'id')])]);

        $data = [
            'contact' => $contact,
            'sms' => $paginated,
            'has_phone_number' => $hasPhoneNumber,
            'phoneNumbersSelect' => $phoneNumbersSelect
        ];

        return view('people.contacts.sms')->with($data);
    }

    public function sendSMS($id, Request $request)
    {
        $contact = Contact::findOrFail($id);
        $SMSPhoneNumber = SMSPhoneNumber::findOrFail(array_get($request, 'sms_phone_number_id'));
        
        $content = new SMSContent();
        array_set($content, 'content', array_get($request, 'content'));
        array_set($content, 'sms_phone_number_from', array_get($SMSPhoneNumber, 'phone_number'));
        array_set($content, 'queued_by', implode('.', [SMSController::class, __FUNCTION__]));
        array_set($content, 'relation_id', array_get($contact, 'id'));
        array_set($content, 'relation_type', get_class($contact));
        array_set($content, 'track_and_tag_events', '[]');

        if( auth()->user()->tenant->sms()->save($content) ){
            $sms = new SMSSent();
            array_set($sms, 'sms_content_id', array_get($content, 'id'));
            array_set($sms, 'from_contact_id', array_get(auth()->user(), 'contact.id'));
            array_set($sms, 'to_contact_id', array_get($contact, 'id'));

            if( auth()->user()->tenant->SMSSent()->save($sms) ){
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'contact' => $id
                    ]); 
                }
                
                return redirect()->back()->with(['message' => 'Your message has been queued. You can recheck this page at any time to see if the message has been sent.']);
            }
        }
        abort(404);
    }
    
    public function directory()
    {
        if (!auth()->user()->can('contacts-directory')) {
            abort(403);
        }
        
        $contacts = Contact::where('is_private', 0);
        if (!auth()->user()->can('contacts-view-under-18')) {
            $contacts = $contacts->whereRaw('(dob is null or TIMESTAMPDIFF(YEAR, dob, now()) >= 18)');
        }
        
        $contacts = $contacts->orderByDirectorySort()->paginate(20);
        
        $sort = Session::get('directorySort', 'last_name');
        $sortType = Session::get('directorySortType', 'asc');
        
        return view('people.contacts.directory')->with(compact('contacts', 'sort', 'sortType'));
    }
    
    public function directorySearch(Request $request)
    {
        if (!auth()->user()->can('contacts-directory') && !auth()->user()->can('group-view')) {
            abort(403);
        }
        
        $search = array_get($request, 'search');
        $searchParam = '%' . $search . '%';
        $groupId = array_get($request, 'group');
        $groupUuid = array_get($request, 'groupUuid');
        $eventUuid = array_get($request, 'event');
        $view = $request->has('view') ? array_get($request, 'view') : 'directory';
        $sort = $request->has('sort') ? array_get($request, 'sort') : 'last_name';
        $sortType = $request->has('sortType') ? array_get($request, 'sortType') : 'asc';
        
        Session::put('directorySort', $sort);
        Session::put('directorySortType', $sortType);
        
        $contacts = Contact::whereRaw("CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''), ' ', IFNULL(email_1,''), IFNULL(company, '')) like ?", [$searchParam]);
                
        if ($groupId) {
            $contacts->whereHas('groups', function ($query) use ($groupId) {
                $query->where('id', $groupId);
            });
        }
        
        if ($groupUuid) {
            $contacts->whereHas('groups', function ($query) use ($groupUuid) {
                $query->where('uuid', $groupUuid);
            });
        }
        
        if ($view === 'directory') {
            $contacts->where('is_private', 0);
            
            if (!auth()->user()->can('contacts-view-under-18')) {
                $contacts->whereRaw('(dob is null or TIMESTAMPDIFF(YEAR, dob, now()) >= 18)');
            }
        }
        
        if ($view === 'checkinPrint') {
            $contacts->whereHas('checkedIn', function ($query) use ($eventUuid) {
                $query->whereHas('event', function ($q) use ($eventUuid) {
                    $q->where('uuid', $eventUuid);
                })->whereHas('tickets', function ($q) {
                    $q->where('printed_tag', 0);
                });
            })->with('primaryContact');
        }
        
        $contacts = $contacts->orderByDirectorySort()->paginate(20);
        
        if ($eventUuid) {
            $contacts->map(function ($contact) use ($eventUuid) {
                $checked = $contact->eventRegistered()->whereHas('event', function ($query) use ($eventUuid) {
                    $query->where('uuid', $eventUuid);
                })->whereHas('tickets', function ($query) {
                    $query->where('checked_in', true);
                })->with('tickets')->get();
                array_set($contact, 'isChecked', count($checked) > 0);
                array_set($contact, 'checked_in_time', array_get($checked, '0.tickets.0.checked_in_time'));
                array_set($contact, 'checked_out_time', array_get($checked, '0.tickets.0.checked_out_time'));
                array_set($contact, 'printed_tag', array_get($checked, '0.tickets.0.printed_tag'));
                return $contact;
            });
        }
        
        if ($view === 'directory') {
            $html = view('people.contacts.includes.contacts-card-list')->with(compact('contacts'))->render();
        } elseif ($view === 'checkin') {
            $event = array_get(CalendarEventTemplateSplit::where('uuid', $eventUuid)->firstOrFail(), 'template');
            $html = view('people.contacts.includes.contacts-checkin')->with(compact('contacts', 'event'))->render();
        } elseif ($view === 'checkinPrint') {
            $event = array_get(CalendarEventTemplateSplit::where('uuid', $eventUuid)->firstOrFail(), 'template');
            $html = view('people.contacts.includes.contacts-checkin-print')->with(compact('contacts'))->render();
        } elseif ($view === 'select') {
            $data = ['contacts' => $contacts];
            
            if (array_get($request, 'membersOfGroup')) {
                $group = array_get($request, 'membersOfGroup') ? Group::find(array_get($request, 'membersOfGroup')) : null;
                
                $contacts->map(function ($contact) use ($group) {
                    $checked = $contact->groups()->where('id', array_get($group, 'id'))->get();
                    array_set($contact, 'isChecked', count($checked) > 0);
                    return $contact;
                });
                
                $data['group'] = $group;
            } 
            
            $html = view('people.contacts.includes.contacts-select')->with($data)->render();
        } else {
            abort(404);
        }
        
        return response()->json(['success' => true, 'count' => $contacts->count(), 'html' => $html, 'lastPage' => $contacts->lastPage()]);
    }
    
    public function storeProfileImage(Contact $contact, Request $request)
    {
        $image = $request->file('image');
        $imageResize = Image::make($image)->resize(400, 400);
        
        if (env('AWS_ENABLED')) {
            if (!empty($contact->profile_image)) {
                Storage::disk('s3')->delete($contact->profile_image);
            }
            
            Storage::disk('s3')->put('profile_images/'.$image->hashName(), $imageResize->stream(), 'public');
            array_set($contact, 'profile_image', 'profile_images/'.$image->hashName());
        } else {
            if (!empty($contact->profile_image)) {
                checkAndDeleteFile(storage_path('app/public/contacts/' . $contact->profile_image));
            }
            
            $imageResize->save(storage_path('app/public/contacts/'.$image->hashName()));
            array_set($contact, 'profile_image', $image->hashName());
        }
        
        $contact->update();
    }
    
    public function showRestore($id)
    {
        if (!auth()->user()->can('contact-view')) {
            abort(403);
        }
        
        $contact = Contact::withTrashed()->findOrFail($id);
        
        if (!$contact->trashed()) {
            return redirect()->route('contacts.show', $contact);
        }
        
        $user = User::find($contact->updated_by);
        
        return view('people.contacts.restore')->with(compact('contact', 'user'));
    }
    
    public function restore($id)
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        
        $this->authorize('delete', $contact);
        
        if ($contact->restore()) {
            return redirect()->route('contacts.show', $contact)->with('message', __('Contact was restored successfully'));
        } else {
            return redirect()->back()->with('error', __('An error occurred, unable to restore contact'));
        }        
    }
    
    public function editProfile()
    {
        if (!auth()->user()->can('edit-profile')) {
            abort(403);
        }
        
        $contact = auth()->user()->contact;
        
        $customFields = CustomFieldValue::where([
            ['relation_id', '=', array_get($contact, 'id')],
            ['relation_type', '=', Contact::class]
        ])->get();

        $data = [
            'contact' => $contact,
            'uid' => Crypt::encrypt($contact->id),
            'customFields' => $customFields,
            'countries' => $this->getCountries(),
            'imagePath' => array_get($contact, 'profile_image') ? $contact->profile_image_src : null,
            'showRemoveButton' => array_get($contact, 'profile_image') ? true : false,
            'editProfile' => true,
            'customFieldsImported' => null
        ];

        return view('people.contacts.edit')->with($data);
    }
    
    public function updateFamily(UpdateContactFamily $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        if (!auth()->user()->canDo('update', $contact)) {
            abort(403);
        }
        
        mapModel($contact, $request->all());
        
        if ($contact->update()) {
            return response()->json(['id' => array_get($contact, 'id'), 'message' => __('Contact family updated successfully'), 'redirect' => route('contacts.show', ['id' => array_get($contact, 'id')])]);
        }
        
        abort(500);
    }
    
    public function updateFamilyPosition(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        if (!auth()->user()->canDo('update', $contact)) {
            abort(403);
        }
        
        mapModel($contact, $request->all());
        
        if ($contact->update()) {
            return response()->json(['message' => __('Family position updated successfully'), 'family_position' => array_get($request, 'family_position')]);
        }
        
        abort(500);
    }
    
    public function manageUnsubscribedPhones(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        $currentPhone = array_get($request, 'phone');
        $phones = array_get($contact, 'unsubscribed_phones');
        $newPhones = [];
        
        if (array_get($request, 'action') === 'resubscribe') {
            foreach ($phones as $phone) {
                if ($phone !== $currentPhone) {
                    $newPhones[] = $phone;
                }
            }
            
            $message = array_get($contact, 'full_name').__(' has been successfully re-subscribed to ').$currentPhone;
        } else {
            $newPhones = $phones;
            
            if (!in_array($currentPhone, $newPhones)) {
                $newPhones[] = $currentPhone;
            }
            
            $message = array_get($contact, 'full_name').__(' has been successfully unsubscribed from ').$currentPhone;
        }
        
        $contact->unsubscribed_from_phones = count($newPhones) > 0 ? implode(',', $newPhones) : null;
        
        if ($contact->update()) {
            return response()->json(['message' => $message, 'unsubscribed_from_phones' => $contact->unsubscribed_from_phones]);
        }
        
        abort(500);
    }
    
    public function storeCustomFields(Contact $contact, Request $request)
    {
        $customFieldsData = json_decode(array_get($request, 'customFieldsData'));
                
        foreach ($customFieldsData as $field => $data) {
            $customField = CustomField::notImported()->where('code', $field)->first();
            
            if ($customField) {
                if (array_get($customField, 'type') === 'multiselect' && $data) {
                    $data = implode(',', $data);
                }
                
                CustomFieldValue::createOrUpdate($data, $contact, $customField);
            }
            
            if (substr($field, '-8') === '__c_date' && $data) {
                $code = str_replace('__c_date', '__c', $field);
                $fulLData = $data.' '.$customFieldsData->{$code.'_time'};
                $customFieldsData->$code = $fulLData;
            }
        }
    }
    
    public function resubscribe($id)
    {
        $contact = Contact::findOrFail($id);
        
        $contact->unsubscribed = null;
        $contact->update();
        
        EmailQueue::set($contact, [
            'from_name' => array_get($contact, 'tenant.organization'),
            'from_email' => array_get($contact, 'tenant.email'),
            'subject' => 'Re-Subscribed to '.array_get($contact, 'tenant.organization'),
            'content' => view('emails.send.contacts.resubscribe')->with(compact('contact'))->render(),
            'model' => $contact,
            'queued_by' => 'contacts.resubscribe'
        ]);
        
        $folder = Folder::findOrCreate('Emails', 'TAGS', $contact->tenant, true);
        $tag = Tag::findOrCreate(array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'), $folder, $contact->tenant, true);
        $contact->tags()->detach(array_get($tag, 'id'));
        
        return response()->json(['success' => true, 'message' => 'Contact was re-subscribed successfully']);
    }
    
    public function unsubscribePermanently($id)
    {
        $contact = Contact::findOrFail($id);
        
        $contact->unsubscribed = Carbon::now();
        $contact->unsubscribed_permanently = Carbon::now();
        $contact->update();
        
        $folder = Folder::findOrCreate('Emails', 'TAGS', $contact->tenant, true);
        $tag = Tag::findOrCreate(array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'), $folder, $contact->tenant, true);
        $contact->tags()->sync(array_get($tag, 'id'), false);
        
        return view('people.contacts.unsubscribed-permanently')->with(compact('contact'));
    }
}
