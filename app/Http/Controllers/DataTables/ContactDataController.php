<?php

namespace App\Http\Controllers\DataTables;

use App\DataTables\ContactDataTable;
use App\DataTables\Scopes;
use App\Models\CalendarEvent;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\DatatableState;
use App\Models\Group;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

class ContactDataController extends Controller {


    /**
     * NOTE: auto-saving is currently turned off 
     * @param  Request $request 
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $uri = 'crm/search/contacts';

            // TODO Restore auto-save load  (see https://app.asana.com/0/1194629996910624/1195398837943913/f)
            return response()->json( ContactDataTable::stateDefaults() );
            return response()->json(
                DatatableState::isUserSearch(false)
                    ->forCurrentUser() // NOTE auto loading needs to scope for current user only (perhaps current session would be better)
                    ->first());
        }
        // NOTE This is loading all user searches, not just those saved by the current user
        $states = DatatableState::isUserSearch()->get();
        return view('saved_state.index', compact('states'));
    }

    public function search(ContactDataTable $dataTable)
    {
        $this->authorize('viewAll',\App\Models\Contact::class);
        $tags = \App\Models\Tag::with('folder')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'folder' => ['id' => $tag->folder_id, 'name' => $tag->folder->name ?: null],
            ];
        })->sortBy('folder')->values();
        $purposes = \App\Models\Purpose::with('parentPurpose')->get();
        $tagfolders = \App\Models\Folder::with('tags')->has('tags')->get();
        $campaigns = \App\Models\Campaign::excludeNone()->get(); 
        $parentpurposes = \App\Models\Purpose::parent()->get();
        $states = DatatableState::isUserSearch()->where('uri','crm/search/contacts')
        ->select('id','name')
        ->orderBy('name')
        ->get();
        $events = CalendarEvent::orderBy('name')->groupBy('name')->selectRaw('name, GROUP_CONCAT(id) as ids')->get()->toArray();
        $events = json_encode(array_map(function ($event) {
            $event['id'] = $event['ids'];
            unset($event['ids']);
            return $event;
        }, $events));
        
        $permissions = json_encode(array_get(auth()->user()->ability([],['transaction-view'],['return_type'=>'array']),'permissions'));
        
        $groups = Group::orderBy('name', 'asc')->get();
        
        $selectedState = DatatableState::isUserSearch()->select('id','name')->find($dataTable->request()->get('state_id'));
        
        $customFields = CustomField::notImported()->orderBy('name')->get();
        
        return $dataTable
        ->render( 'people.contacts.advancedsearch',
            compact('tagfolders','campaigns','parentpurposes','states','purposes','tags','events', 'permissions', 'groups', 'selectedState', 'customFields') );
    }

    public function store(Request $request)
    {
        if (!$request->get('is_user_search')) {
            extract($this->createNewState($request));
            return response()->json(compact('result','state'));
        }

        extract($this->createNewList($request));
        return response()->json(compact('result','state','list'));
    }

    // Loads the state specified by the provided id
    public function show($id)
    {
        if (!request()->ajax()) return redirect()->route('search.contacts',['state_id'=>$id]);

        $uri = 'crm/search/contacts';

        return response()->json( DatatableState::where(compact('uri','id'))->first() );
    }

    public function destroy($id)
    {
        $state = DatatableState::findOrFail($id);
        $state->delete();
        
        return response()->json(['result' => 'success']);
    }
    
    public function storeCommunication(Request $request) {
        extract($this->createNewList($request));

        $communication = \App\Models\Communication::create([
            'list_id' => $list->id,
            'uuid' => Uuid::uuid4(),
            'include_public_link' => 1,
            'email_editor_type' => 'none',
            'timezone' => session('timezone'),
            'send_to_all' => 1
        ]);
        
        $communication->refresh();
        
        return response()->json(compact('state','list','communication'));
    }

    public function storeSMS(Request $request) {
        extract($this->createNewList($request));

        $sms = \App\Models\SMSContent::create([
            'list_id' => $list->id,
            'send_to_all' => 1
        ]);
        $sms->refresh();

        return response()->json(compact('state','list','sms'));
    }

    protected function createNewState(Request $request)
    {
        // First search for existing search
        if ($request->has('id')) {
            $state = DatatableState::find($request->id);
        }else{
        $values = ContactDataTable::stateDefaults($request->only(['is_user_search', 'name']), false);
        if (!$values['is_user_search']) $values['is_user_search'] = 0;

            $state = DatatableState::firstOrCreate($values);
        }

        mapModel($state, $request);
        $result = $state->save();
        $state->refresh();

        return compact('result', 'state');
    }

    protected function createNewList(Request $request)
    {
        if ($request->has('updateState')) {
            $state = DatatableState::find($request->updateState);
            mapModel($state, $request);
            $result = $state->save();
        }
        
        if ($request->has('id')) $state = DatatableState::find($request->id);
        if (!isset($state)) extract($this->createNewState($request));

        $list = count($state->list)
            ? $state->list
            : $list = \App\Models\Lists::firstOrCreate([
                'name' => $state->name,
                'datatable_state_id' => $state->id
            ]);
        $list->refresh();

        return compact('result','state','list');
    }
    
    public function downloadPictureDirectory($id)
    {
        $state = DatatableState::findOrFail($id);
        $dataTable = ContactDataTable::createFromState($state);
        
        $contacts = Contact::whereIn('id', $dataTable->getContactIdArray())->orderBy('first_name')->orderBy('last_name')->get();
        
        PDF::setOptions([
            'enable_remote' => true
        ]);
        $pdf = PDF::loadView('people.contacts.includes.picture-directory-pdf', compact('contacts'));

        return $pdf->download(ucfirst(array_get($state, 'name')).' - '.__('Picture Directory').'.pdf');
    }
    
    public function excel($id)
    {
        ini_set('memory_limit', '1024M');
        
        $state = DatatableState::findOrFail($id);
        $dataTable = ContactDataTable::createFromState($state);
        
        $columns = array_get($state, 'columns');
        $columnNames = $dataTable->getTableColumns();
        $visibleColumns = [];
        
        for ($i=0; $i<count($columns); $i++) {
            if (array_get($columns[$i], 'visible') === 'true' && $columnNames[$i] !== 'link') {
                $visibleColumns[] = $columnNames[$i];
            }
        }
        
        $contacts = $dataTable->ajax()->getData()->data;
        
        $tail = str_replace(':', '', displayLocalDateTime(Carbon::now()->toDateTimeString())->toDateTimeString());
        $tail = str_replace('-', '', $tail);
        $tail = str_replace(' ', '-', $tail);
        $filename = substr(implode('-', ['contacts', $tail]), 0, 28);
        $data = [
            'contacts' => $contacts,
            'filename' => $filename,
            'columns' => $visibleColumns,
            'numberColumns' => $dataTable->numberColumns,
            'dateColumns' => $dataTable->dateColumns,
            'alphabet' => range('A', 'Z')
        ];

        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('people.contacts.excel-search', $data);
            });
        })->download('xlsx');
    }
    
    public function loadTotals($id)
    {
        $state = DatatableState::findOrFail($id);
        $dataTable = ContactDataTable::createFromState($state);
        $contacts = $dataTable->ajax()->getData()->data;
        
        $total = 0;
        $lifeTime = 0;
        
        foreach ($contacts as $contact) {
            $total+= toCurrencyReverse($contact->total_amount);
            $lifeTime+= toCurrencyReverse($contact->lifetime_total);
        }
        
        return response()->json(['total' => '$'.to_currency($total), 'lifetime' => '$'.to_currency($lifeTime)]);
    }
}
