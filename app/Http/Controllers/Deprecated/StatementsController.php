<?php

namespace App\Http\Controllers\Deprecated;
use App\Classes\Email\EmailQueue;
use App\Http\Controllers\Controller;

use App\Models\Communication;
use App\Models\StatementTemplate;
use App\Models\StatementTracking;
use App\Models\Contact;
use App\Models\TransactionSplit;
use App\Models\PrintedCommunication;

use App\Http\Requests\Statements\UpdateStatement;
use App\Http\Requests\Statements\StoreStatement;

use App\Classes\Statements\Statement;
use App\Classes\MissionPillarsLog;
use App\Constants;

use App\Traits\CommunicationTrait;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class StatementsController extends Controller {

    const FIND = [
        '[:name:]',
        '[:first-name:]',
        '[:first-name:]',
        '[:last-name:]',
        '[:start_date:]',
        '[:end_date:]',
        '[:organization_name:]',
        '[:ein:]',
        '[:date_today:]',
        '[:total_amount:]',
        '[:last_transaction_date:]',
        '[:last_transaction_amount:]',
        '[:last_transaction_purpose:]',
        '[:contact_id:]'
    ];
    const SPECIAL_SEARCH = [
        '[:address:]',
        '[:item_list:]',
        '[:list_of_donations:]',
        '[:funds_sumary:]',
        '[:pledges_summary:]'
    ];
    const PAYMENT_OPTIONS = [
        'cc' => 'Credit Card',
        'cash' => 'Cash',
        'check' => 'Check',
        'ach' => 'Automated Clearing House'
    ];

    const PRINT_FOR = [
                /* 'all_donors' => 'All Donors', */
                'donors_with_address' => 'All contacts with mailing address',
                'donors_without_email' => 'All contacts without email addresses',
                'donors_not_marked_paper_statement' => 'Contacts not marked paper statement',
                'donors_marked_paper_statement' => 'Only contacts marked paper statements'
            ];

    const PERMISSION = 'crm-communications';

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
    public function index() {
        return redirect()->route('communications.index');
        $data = [
            'statements' => StatementTracking::whereNotNull('id')->orderBy('id', 'desc')->paginate(),
            // 'statements' => StatementTracking::orderBy('id', 'desc')->paginate(),
            'print_for' => self::PRINT_FOR
        ];

        return view('contributions.statements.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return redirect()->route('communications.create');
        $templates = StatementTemplate::all();

        $data = [
            'uuid' => Uuid::uuid4(),
            'start' => Carbon::now()->startOfYear()->toDateString(),
            'end' => Carbon::now()->endOfDay()->toDateString(),
            'templates' => $templates,
            'print_for' => self::PRINT_FOR,
            'statement' => null
        ];

        return view('contributions.statements.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStatement $request) {
        $startDate = Carbon::parse(array_get($request, 'start_date'));
        $endDate = Carbon::parse(array_get($request, 'end_date'));
        // $uuid = Uuid::uuid1();

        $statement = mapModel(new StatementTracking(), $request->all());
        array_set($statement, 'use_date_range', false);
        if(!is_null(array_get($request, 'use_date_range'))){
            array_set($statement, 'use_date_range', true);
        }

        array_set($statement, 'start_date', $startDate->startOfDay()->toDateTimeString());
        array_set($statement, 'end_date', $endDate->endOfDay()->toDateTimeString());
        // array_set($statement, 'uuid', $uuid);
        array_set($statement, 'content', array_get($request, 'statement'));

        if (auth()->user()->tenant->statementTraking()->save($statement)) {
            $communication = Communication::find($statement->id);
            $communication->include_transactions = true;
            if ($request->isEmail == true) {
                $res = $this->sendEmail($request, $statement, $communication);
                return response()->json($res);
            }
            elseif ( array_get($statement, 'print_for') === 'contact' ) {
                $communicationContent = [
                    'tenant_id' => array_get($communication, 'tenant_id'),
                    'subject' => array_get($communication, 'subject'),
                    'content' => array_get($communication, 'print_content'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                $communication_content_id = DB::table('communication_contents')->insertGetId($communicationContent);
                
                $communication->printRecipients()->attach($request->get('contact_id'), compact('communication_content_id'));
                // Setting up relationship to list and communication listing filtering
                $communication->relation_id = $request->get('contact_id');
                $communication->relation_type = Contact::class;
                $communication->timezone = session('timezone');
                $communication->label = array_get($communication, 'subject');
                $communication->save();
                $params = [
                    'uuid' => array_get($statement, 'uuid'),
                    'id' => array_get($statement, 'id'),
                    'download' => array_get($request, 'download'),
                    'contact_id' => array_get($request, 'contact_id')
                ];

                return redirect()->route('print-mail.preview', $params);
            }
            $communication->save();
            return redirect()->route('print-mail.show', ['id' => array_get($statement, 'id')]);
        }
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $statement = StatementTracking::findOrFail($id);
        $contact = null;
        if(array_get($statement, 'print_for') === 'contact'){
            $contact = array_get($statement, 'contacts.0');
        }
        $data = [
            'statement' => $statement,
            'contact' => $contact,
            'print_for' => self::PRINT_FOR,
        ];

        return view('contributions.statements.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request) {
        return redirect()->route('communications.edit',$id);
        $statement = StatementTracking::findOrFail($id);
        $templates = StatementTemplate::all();
        $contact_id = null;
        if (array_get($statement, 'print_for') === 'contact') {
            $print_for = ['contact' => 'Contact'];
            $contact_id = array_get($statement, 'contacts.0.id');
        } else {
            $print_for = self::PRINT_FOR;
        }

        $data = [
            'statement' => $statement,
            'uuid' => Uuid::uuid4(),
            'start' => Carbon::parse(array_get($statement, 'start_date'))->toDateString(),
            'end' => Carbon::parse(array_get($statement, 'end_date'))->toDateString(),
            'templates' => $templates,
            'print_for' => $print_for,
            'contact_id' => $contact_id
        ];

        return view('contributions.statements.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStatement $request, $id) {
        $statement = StatementTracking::findOrFail($id);
        mapModel($statement, $request->all());
        array_set($statement, 'use_date_range', false);
        if(!is_null(array_get($request, 'use_date_range'))){
            array_set($statement, 'use_date_range', true);
        }
        array_set($statement, 'content', array_get($request, 'statement'));
        if ($statement->update()) {
            $params = [
                'id' => array_get($statement, 'id'),
            ];

            if (!is_null(array_get($request, 'contact_id'))) {
                array_set($params, 'contact_id', array_get($request, 'contact_id'));
            }

            return redirect()->route('print-mail.show', $params);
        }
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function getStatementTemplate(Request $request) {
        $statement = StatementTemplate::findOrFail(array_get($request, 'id'));

        return response()->json(array_get($statement, 'print_content'));
    }

    /**
     * Deprecated. Only used for report
     */
    // private function getStatementData($uuid, $id, Request $request, $report = false) {
    private function getStatementData($id, Request $request, $report = false) {
        MissionPillarsLog::deprecated();
        $statement = StatementTracking::findOrFail($id);
        $not_in_statement_but_has_donations = [];
        $not_in_statement = [];

        $start = Carbon::parse(array_get($statement, 'start_date'));
        $end = Carbon::parse(array_get($statement, 'end_date'));
        
        if (array_get($statement, 'print_for') == 'contact') {
            $contacts = Contact::whereHas('transactions', function($query) use($start, $end){
                $query->whereHas('splits', function($query){
                    $query->where('tax_deductible', true);
                })->where('status', 'complete')
                ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->where('id', array_get($request, 'contact_id'))->get();
        }
        // elseif (array_get($statement, 'print_for') == 'all_donors') {
        //     $all_donors = Statement::getAllDonorsData($start, $end, $report, $statement);
        //     $contacts = array_get($all_donors, 'contacts', []);
        //     $not_in_statement_but_has_donations = array_get($all_donors, 'not_in_statement_but_has_donations', []);
        //     $not_in_statement = array_get($all_donors, 'not_in_statement', []);
        // }
        else {
            switch (array_get($statement, 'print_for')) {
                case 'donors_with_address':
                    $all_donors = Statement::getDonorsWithAddress($start, $end, $report, $statement);
                    break;
                case 'donors_without_email':
                    $all_donors = Statement::getDonorsWithoutEmailAddress($start, $end, $report, $statement);
                    break;
                case 'donors_not_marked_paper_statement':
                    $all_donors = Statement::getDonorsNotMarkedPaperStatement($start, $end, $report, $statement);
                    break;
                case 'donors_marked_paper_statement':
                    $all_donors = Statement::getDonorsMarkedPaperStatement($start, $end, $report, $statement);
                    break;
                default:
                    abort();
            }
            $contacts = array_get($all_donors, 'contacts', []);
            $not_in_statement_but_has_donations = array_get($all_donors, 'not_in_statement_but_has_donations', []);
            $not_in_statement = array_get($all_donors, 'not_in_statement', []);
        }

        if(array_get($statement, 'use_date_range') == 1){
            foreach ($contacts as $contact) {
                $splits = TransactionSplit::whereHas('transaction', function($query) use($contact, $start, $end) {
                    $query->where([
                                ['status', '=', 'complete'],
                                ['contact_id', '=', array_get($contact, 'id')]
                            ])
                            ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
                })->where('tax_deductible', true)->get();
                array_set($contact, 'donations', $splits);
            }
        }
        else{
            array_set($contact, 'donations', null);
        }

        $data = [
            'statement' => $statement,
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement,
            'find' => self::FIND,
            'special_search' => self::SPECIAL_SEARCH,
            'payment_options' => self::PAYMENT_OPTIONS,
            'contact_id' => array_get($request, 'contact_id')
        ];

        return $data;
    }

    /**
     * Obtains communication and adds transaction data
     * @param  [integer]  $id   
     * @param  Request $request 
     * @return [array]           
     */
    private function getCommunicationData($id, Request $request) {
        $contact_id = $request->get('contact_id');
        $statement = Communication::findOrFail($id);
        $filters = CommunicationTrait::getTransactionFilters($statement);

        // If print not "generated" already at least once, do it now
        if (!$statement->recipients()->count()) {
            $statement->trackPrintedContacts();
        }
        // TODO Consider requesting a specific communication batch from request
        $batch = $statement->recipients()->max('batch'); // get the latest batch
        $contacts = $statement->recipients()->wherePivot('batch', $batch)->get();
        
        if($statement->include_transactions && $statement->use_date_range){
            foreach ($contacts as $contact) {
                appendTransactionsToContact($contact, $statement->transaction_start_date, $statement->transaction_end_date, $filters);
            }
        }

        return compact( 'statement', 'contacts', 'contact_id' );
    }

    public function previewStatement($id, Request $request) {
        // $data = $this->getStatementData($id, $request);
        $data = $this->getCommunicationData($id, $request);
        // $this->track($data);
        return view('contributions.statements.print')->with($data);
    }

    public function pdfStatement($id, Request $request) {
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '256M');
        
        // $data = $this->getStatementData($id, $request);
        $data = $this->getCommunicationData($id, $request);
        array_set($data, 'download', true);
        $filename = 'Statement_' . array_get($data, 'statement.start_date') . '-' . array_get($data, 'statement.end_date') . '.pdf';
        $pdf = PDF::loadView('contributions.statements.pdf', $data);
        return $pdf->download($filename);
        //$pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('contributions.statements.pdf', $data);
        //return $pdf->stream($filename, ["Attachment" => false]);
    }

    /**
     * Deprecated. tracks which contacts have be included in print
     * @param  [array] $data [description]
     */
    private function track($data) {
        MissionPillarsLog::deprecated();
        $statement = array_get($data, 'statement');
        $contacts = array_get($data, 'contacts');
        $statement->contacts()->attach(array_pluck($contacts, 'id'));
    }

    public function reportStatement($id, Request $request) {
        MissionPillarsLog::deprecated();
        // $data = $this->getStatementData($uuid, $id, $request, true);
        $data = $this->getStatementData($id, $request, true);
        $contact = array_get($data, 'statement.contacts.0');
        array_set($data, 'contact', $contact);
        array_set($data, 'tab', array_get($request, 'tab'), 1);
        array_set($data, 'print_for', self::PRINT_FOR);
        return view('contributions.statements.report')->with($data);
    }

    private function sendEmail(StoreStatement $request, StatementTracking $statement, Communication $communication)
    {
        $communication->content = $statement->content;
        $communication->relation_id = $request->get('contact_id');
        $communication->relation_type = Contact::class;
        $communication->reply_to = $request->get('reply_to');
        $communication->from_name = $request->get('from_name');
        $communication->cc_secondary = boolval($request->get('cc_secondary')) ? 1 : null;
        $communication->timezone = session('timezone');
        $communication->save();
        
        $communicationContent = [
            'tenant_id' => array_get($communication, 'tenant_id'),
            'subject' => array_get($communication, 'subject'),
            'content' => array_get($communication, 'content'),
            'editor_type' => array_get($communication, 'email_editor_type'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $communicationContentId = DB::table('communication_contents')->insertGetId($communicationContent);
        
        EmailQueue::queue(Contact::find($request->get('contact_id')), $communication, $communicationContentId);
        return true;
    }

    public function viewPrint(Request $request)
    {
        $id = array_get($request, 'id');
        
        $print = PrintedCommunication::findOrFail($id);
        $communicationContent = array_get($print, 'communicationContent');
        $communication = Communication::findOrFail(array_get($print, 'communication_id'));
        $filters = CommunicationTrait::getTransactionFilters($communication);
        $contact = $communication->recipients()->wherePivot('contact_id', array_get($print, 'contact_id'))->wherePivot('batch', array_get($print, 'batch'))->firstOrFail();
        
        if($communication->include_transactions && $communication->use_date_range){
            appendTransactionsToContact($contact, $communication->transaction_start_date, $communication->transaction_end_date, $filters);
        }
        
        $data = [
            'print_content' => array_get($communicationContent, 'content'),
            'contact'=> $contact,
            'communication' => $communication
        ];
        
        $html = view('contributions.statements.pdf-timeline', $data)->render();
        
        return response()->json(['success' => true, 'label' => array_get($communicationContent, 'subject'), 'content' => $html]);
    }
}
