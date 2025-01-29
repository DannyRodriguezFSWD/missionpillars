<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Traits\TagsTrait;
use App\Constants;
use App\Models\Group;
use App\Models\Address;
use App\Traits\CountriesTrait;
use App\Http\Requests;
use App\Models\Contact;
use App\Models\Form;
use App\Models\Campaign;
use App\Models\Purpose;
use App\Models\DatatableState;
use App\Models\Lists;
use App\Traits\Groups\MembersManagment;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

class GroupsController extends Controller {

    use TagsTrait,
        CountriesTrait,
        MembersManagment;
    const PERMISSION = 'crm-groups';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if (!auth()->user()->can('group-view')) abort(403);
        $root = Folder::find(array_get(Constants::GROUPS_SYSTEM, 'FOLDERS.ALL_GROUPS'));
        $groups = Group::orderBy('name')->paginate();
        $myGroups = Group::whereHas('contacts', function ($query) {
            $query->where('id', auth()->user()->contact->id);
        })->get()->pluck('id');
        
        return view('people.groups.index')->with(compact('root', 'groups', 'myGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id) {
        if (!auth()->user()->can('group-create')) abort(403);
        $chart = Purpose::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        $form = Form::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        
        $countries = $this->getCountries();
        $folderDropdown = collect(Folder::where('type', 'GROUPS')->orderBy('name')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $forms = collect(Form::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($form, 'id', 1) => array_get($form, 'name', 'None')]);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($chart, 'id', 1) => 'None']);

        $data = [
            'root' => Folder::findOrFail($id),
            'countries' => $countries,
            'folderDropdown' => $folderDropdown,
            'forms' => $forms,
            'campaigns' => $campaigns,
            'charts' => $charts,
            'group' => null,
            'imagePath' => null,
            'showRemoveButton' => false,
            'manager' => null
        ];

        if (request()->has('contact')) {
            $contact = Contact::findOrFail(request()->get('contact'));
            array_set($data, 'contact', $contact);
        }
        
        return view('people.groups.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\Groups\Store $request) {
        if (array_get($request, 'contact_id') === 'current_contact') {
            array_set($request, 'contact_id', auth()->user()->contact->id);
        }
        
        $group = mapModel(new Group(), $request->all());
        array_set($group, 'description', array_get($request, 'content'));
        array_set($group, 'uuid', Uuid::uuid1());
        
        if (!auth()->user()->tenant->groups()->save($group)) { abort(500); }
        
        $address = mapModel(new Address(), $request);
        array_set($address, 'relation_id', array_get($group, 'id'));
        array_set($address, 'relation_type', Group::class);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $file->store('public/groups');
            array_set($group, 'cover_image', $file->hashName());
            $group->update();
        }
        
        if (!auth()->user()->tenant->addresses()->save($address)) { abort(500); }
        if ($request->has('cid')) {
            try {
                $contact = Crypt::decrypt(array_get($request, 'cid'));
                return response()->json(['id' => array_get($group, 'id'),'message' => 'Group Created Successfully!', 'redirect' => route('contacts.groups', ['id' => $contact, 'folder' => array_get($request, 'folder_id')])]);
            } catch (Illuminate\Contracts\Encryption\DecryptException $ex) {
                return redirect()->route('cheating');
            }
        }
        
        return response()->json(['id' => array_get($group, 'id'), 'group' => $group, 'message' => 'Group Created Successfully!', 'redirect' => route('groups.show', ['id' => array_get($group, 'id')])]);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) 
    {
        if (!auth()->user()->can('group-view')) {
            abort(403);
        }
        
        $group = Group::findOrFail($id);
        $data = $this->getData($group, request());
        
        array_set($data, 'showMembers', false);

        $sort = Session::get('directorySort', 'last_name');
        $sortType = Session::get('directorySortType', 'asc');
        
        array_set($data, 'sort', $sort);
        array_set($data, 'sortType', $sortType);
        
        return view('people.groups.show')->with($data);
    }
    
    /**
     * Display the specified FOLDER (NOT a GROUP)
     */
    public function showFolder($id) {
        if (!auth()->user()->can('group-view')) abort(403);
        $root = Folder::where('id', array_get(Constants::GROUPS_SYSTEM, 'FOLDERS.ALL_GROUPS'))->first();
        $data = $this->getDataTree($root, $id, 'GROUPS');
        $countries = $this->getCountries();
        array_set($data, 'countries', $countries);

        $folderDropdown = collect(Folder::where('type', 'GROUPS')->orderBy('name')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);

        return view('people.groups.index')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        if (!auth()->user()->can('group-update')) abort(403);
        $group = Group::findOrFail($id);
        $countries = $this->getCountries();
        $chart = Purpose::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        $form = Form::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        
        $folderDropdown = collect(Folder::where('type', 'GROUPS')->orderBy('name')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        
        $forms = collect(Form::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($form, 'id', 1) => array_get($form, 'name', 'None')]);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($chart, 'id', 1) => 'None']);
        
        $data = [
            'root' => Folder::findOrFail(array_get($group, 'folder_id', 0)),
            'group' => $group,
            'countries' => $countries,
            'forms' => $forms,
            'campaigns' => $campaigns,
            'charts' => $charts,
            'folderDropdown' => $folderDropdown,
            'imagePath' => array_get($group, 'cover_image') ? 'storage/groups/'.array_get($group, 'cover_image') : null,
            'showRemoveButton' => array_get($group, 'cover_image') ? true : false,
            'manager' => ['id' => array_get($group, 'manager.id'), 'name' => array_get($group, 'manager.first_name').' '.array_get($group, 'manager.last_name').' ('.array_get($group, 'manager.email_1').')']
        ];
        
        return view('people.groups.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\Groups\Update $request, $id) {
        $group = Group::findOrFail($id);
        mapModel($group, $request->all());
        array_set($group, 'description', array_get($request, 'content'));
        $group->update();
        
        if ($request->has('removeCoverImage')) {
            checkAndDeleteFile(storage_path('app/public/groups/' . $group->cover_image));
            $group->update(['cover_image' => null]);
        }

        if ($request->hasFile('image') && $request->file('image')->isValid() && !$request->has('removeCoverImage')) {
            if (!empty($group->cover_image)) {
                unlink(storage_path('app/public/groups/' . $group->cover_image));
            }

            $file = $request->file('image');
            $file->store('public/groups');
            array_set($group, 'cover_image', $file->hashName());
            $group->update();
        }
        
        $groupAddress = $group->addresses->first();
        $address = mapModel($groupAddress, $request);
        array_set($address, 'relation_id', array_get($group, 'id'));
        array_set($address, 'relation_type', Group::class);
        if (!auth()->user()->tenant->addresses()->save($address)) { abort(500); }
        
        return response()->json(['id' => array_get($group, 'id'),'message' => 'Group Updated Successfully!', 'redirect' => route('groups.show', ['id' => array_get($group, 'id')])]);
    }

    public function members($id, Request $request) 
    {
        if (!auth()->user()->can('group-view')) {
            abort(403);
        }
        
        $group = Group::findOrFail($id);
        $data = $this->getData($group, request());
        array_set($data, 'showMembers', true);

        return view('people.groups.show')->with($data);
    }

    public function getTagPath($id, $path = []) {
        $folder = Folder::find($id);
        if (!is_null($folder->folder_parent_id)) {
            array_push($path, $folder);
            return $this->getTagPath($folder->folder_parent_id, $path);
        }
        array_push($path, $folder);
        return array_reverse($path);
    }

    public function address($id, Request $request) {
        if (!auth()->user()->can('group-view')) abort(403);
        $group = Group::findOrFail($id);
        $countries = $this->getCountries();
        $data = [
            'group' => $group,
            'countries' => $countries
        ];
        return view('people.groups.create_address')->with($data);
    }

    public function editAddress($id, $aid) {
        if (!auth()->user()->can('group-update')) abort(403);
        $address = Address::findOrFail($aid);
        $group = Group::findOrFail($id);
        $countries = $this->getCountries();
        $data = [
            'id' => $id,
            'address' => $address,
            'group' => $group,
            'countries' => $countries
        ];

        return view('people.groups.edit_address')->with($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        if (!auth()->user()->can('group-delete')) abort(403);
        Group::destroy($id);
        return redirect()->route('groups.index')->with('message', __('Group succesfully deleted'));
    }

    public function sync($id, Request $request) 
    {
        if (!auth()->user()->can('group-update')) {
            abort(403);
        }
        
        $group = Group::findOrFail($id);
        
        $add = array_get($request, 'add');
        $remove = array_get($request, 'remove');
        
        if ($add) {
            $group->contacts()->sync($add, false);
        }
        
        if ($remove) {
            $group->contacts()->detach($remove);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function syncUuid($id, Request $request) 
    {
        if (!auth()->user()->can('group-update')) {
            abort(403);
        }
        
        $group = Group::where('uuid', $id)->firstOrFail();
        $contact = Contact::findOrFail(array_get($request, 'contact_id'));
        
        if (array_get($request, 'action') === 'add') {
            $group->contacts()->sync([array_get($contact, 'id')], false);
        }
        
        if (array_get($request, 'action') === 'remove') {
            $group->contacts()->detach([array_get($contact, 'id')]);
        }
        
        return response()->json(['success' => true]);
    }

    public function email($id)
    {
        $group = Group::findOrFail($id);
        
        $datatableStateValues = [
            'uri' => 'crm/search/contacts',
            'name' => 'saveSearch_list_'. strtotime(date('Y-m-d H:i:s')),
            'is_user_search' => 0,
            'time' => strtotime(date('Y-m-d H:i:s')),
            'search' => '{"search":null,"smart":"true","regex":"false","caseInsensitive":"true","transaction_date_min":null,"transaction_date_max":null,"transaction_amount_min":null,"transaction_amount_max":null,"transaction_amount_use_sum":"1","groups":["'.$group->id.'"]}'
        ];
        
        $datatableState = DatatableState::create($datatableStateValues);
        
        $listData = [
            'name' => 'saveSearch_list_'. strtotime(date('Y-m-d H:i:s')),
            'datatable_state_id' => $datatableState->id
        ];
        
        $list = Lists::create($listData);
        
        $communication = \App\Models\Communication::create([
            'list_id' => $list->id,
            'uuid' => Uuid::uuid4(),
            'include_public_link' => 1,
            'email_editor_type' => 'none',
            'timezone' => session('timezone'),
            'send_to_all' => 1
        ]);
        
        return redirect()->route('communications.edit', $communication);
    }
    
    public function sms($id)
    {
        $group = Group::findOrFail($id);
        
        $datatableStateValues = [
            'uri' => 'crm/search/contacts',
            'name' => 'saveSearch_list_'. strtotime(date('Y-m-d H:i:s')),
            'is_user_search' => 0,
            'time' => strtotime(date('Y-m-d H:i:s')),
            'search' => '{"search":null,"smart":"true","regex":"false","caseInsensitive":"true","transaction_date_min":null,"transaction_date_max":null,"transaction_amount_min":null,"transaction_amount_max":null,"transaction_amount_use_sum":"1","groups":["'.$group->id.'"]}'
        ];
        
        $datatableState = DatatableState::create($datatableStateValues);
        
        $listData = [
            'name' => 'saveSearch_list_'. strtotime(date('Y-m-d H:i:s')),
            'datatable_state_id' => $datatableState->id
        ];
        
        $list = Lists::create($listData);
        
        $sms = \App\Models\SMSContent::create([
            'list_id' => $list->id
        ]);
        
        return redirect('/crm/communications/sms/'.$sms->id.'/edit');
    }
    
    public function excel($id)
    {
        $group = Group::with('contacts.addresses')->findOrFail($id);
        
        $contacts = $group->contacts()->orderByDirectorySort()->get();
        
        $tail = str_replace(':', '', displayLocalDateTime(Carbon::now()->toDateTimeString())->toDateTimeString());
        $tail = str_replace('-', '', $tail);
        $tail = str_replace(' ', '-', $tail);
        $filename = substr(implode('-', [array_get($group, 'name'), $tail]), 0, 28);
        
        Excel::create($filename, function($excel) use ($filename, $contacts) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($filename, $contacts) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('people.contacts.excel', compact('filename', 'contacts'));
                $sheet->setColumnFormat(array(
                    'B' => '0.00',
                ));
            });
        })->download('xlsx');
    }
    
    public function search(Request $request)
    {
        if (array_get($request, 'search')) {
            $groups = Group::where('name', 'like', '%'.array_get($request, 'search').'%')->orderBy('name')->paginate();
        } else {
            $groups = Group::orderBy('name')->paginate();
        }
        
        $myGroups = Group::whereHas('contacts', function ($query) {
            $query->where('id', auth()->user()->contact->id);
        })->get()->pluck('id');
        
        $html = view('people.groups.includes.groups-card-list')->with(compact('groups', 'myGroups'))->render();
        
        return response()->json(['success' => true, 'count' => $groups->count(), 'html' => $html, 'lastPage' => $groups->lastPage()]);
    }
    
    public function downloadPictureDirectory($id)
    {
        $group = Group::with('contacts.addresses')->findOrFail($id);
        
        $contacts = $group->contacts()->orderByDirectorySort()->get();
        
        PDF::setOptions([
            'enable_remote' => true
        ]);
        $pdf = PDF::loadView('people.contacts.includes.picture-directory-pdf', compact('group', 'contacts'));

        return $pdf->download(array_get($group, 'name').' - '.__('Picture Directory').'.pdf');
    }
}
