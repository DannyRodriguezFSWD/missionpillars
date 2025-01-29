<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use DB;

use App\Models\Email;
use App\Models\Communication;
use App\Models\Document;
use App\Models\SMSContent;
use App\Models\StatementTemplate;
use App\Models\StatementTracking;
use App\Models\Lists;
use App\Models\Contact;

use App\Classes\Shared\Emails\Charts\Pie\PieChart as PieChart;
use App\Classes\MissionPillarsLog;
use App\Constants;
use App\Classes\Tiny\Tiny;

use App\Traits\Emails\EmailTrait;

use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Barryvdh\DomPDF\Facade as PDF;

class CommunicationsController extends Controller
{
    use EmailTrait;

    const PERMISSION = 'crm-communications';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        }, ['except' => ['publicView']]);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $communications = Communication::select([
            'communications.id',
            'l.name as list_name',
            'l.datatable_state_id',
            'communications.subject as content',
            'communications.label',
            'communications.created_at',
            'old_print_for AS print_for',
            // counting DISTINCT contacts that have received communication (EITHER email or print)
            DB::raw('COUNT(DISTINCT IFNULL(es.contact_id, cc.contact_id)) AS sent_count'),
            'communications.last_action',
            'communications.time_scheduled',
            'communications.updated_at'
        ])
        ->leftJoin('lists AS l', 'l.id', '=', 'communications.list_id')
        ->leftJoin('email_sent AS es', 'communications.id', '=', 'es.email_content_id')
        ->leftJoin('communication_contact AS cc', 'communications.id', '=', 'cc.communication_id')
        ->where('relation_type', Lists::class) // ONLY show communications sent to list or 'Everyone' (exclude e.g., contact communication)
        ->orWhereHas('list',function($list) {
            $list->savedSearch(); // also include any communication started from Saved search
        })
        ->orderBy('created_at','DESC')
        ->groupBy('communications.id');
        if ($request->get('statements')) $communications->includesTransactions();

        // pagination
        $page = array_get($request, 'page', 1);
        $perPage = 15;
        $collection = collect($communications->get());
        $messages = new LengthAwarePaginator($collection->forPage($page, $perPage), $collection->count(), $perPage, $page, ['path'=>route('communications.index')]);

        return view('communications.index')->with(compact('messages'));
    }

    /**
    * We are already storing the resource during creation so this will actually redirect us to the edit view
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $communication = new Communication();
        $communication->tenant_id = auth()->user()->tenant_id;
        $communication->relation_type = Lists::class;
        $communication->uuid = Uuid::uuid4();
        $communication->include_transactions = 1;
        $communication->include_public_link = 1;
        $communication->email_editor_type = request()->has('create_contribution_statement') ? 'tiny' : 'none';
        $communication->timezone = session('timezone');
        $communication->send_to_all = 1;
        if (request()->has('create_contribution_statement')) request()->session()->flash('create_contribution_statement', '1');
        $communication->save();

        return redirect(route('communications.edit', $communication->id));
    }


    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $communication = new Communication();
        $communication->tenant_id = auth()->user()->tenant_id;
        $communication->save();

        $communication = mapModel($communication, $request->get('communication', $request->all())); // get communication from request or assume entire request is communication
        // List
        if (!$communication->list_id) $communication->list_id = null;
        // Setting up relationship to list and communication listing filtering
        $communication->relation_id = $communication->list_id;
        $communication->relation_type = Lists::class;
        $communication->uuid = Uuid::uuid4();
        $communication->timezone = session('timezone');
        $communication->send_to_all = 1;

        $this->updateTagFields($communication, $request);

        $communication->save();
        $redirect_route_name = $this->getConfigureRoute($request);
        if (!$redirect_route_name) abort(400, 'Must submit and either configure email or print');

        return redirect(route($redirect_route_name,$communication->id));
    }


    /**
    * Display the specified resource.
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id, Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        
        $communication = Communication::find($id);
        if (!$communication) abort(403,'Communication not found');
        $totalemails = $communication->sentEmails()->count();
        // $totalemailrecipients = $communication->sentEmails()->distinct()->count('contact_id');
        $emailrecipients = $communication->sentEmails()->distinct()->pluck('contact_id');
        $totalemailrecipients = $emailrecipients->count();
        $totalprinted = $communication->printRecipients()->count();
        // $totalprintrecipients = $communication->printRecipients()->distinct()->count('contact_id');
        $printrecipients = $communication->printRecipients()->distinct()->pluck('contact_id');
        $totalprintrecipients = $printrecipients->count();
        $totalrecipients = ($emailrecipients->merge($printrecipients))->unique()->count();

        if (array_get($communication, 'time_scheduled') && strtotime(array_get($communication, 'time_scheduled')) > strtotime(date('Y-m-d H:i:s'))) {
            $totalEmailsScheduled = $communication->sentEmails()->where('sent', 0)->count();
        } else {
            $totalEmailsScheduled = 0;
        }
        
        $html = view('communications.show')->with(compact('communication',
        'totalemails','totalemailrecipients',
        'totalprinted','totalprintrecipients', 'totalEmailsScheduled',
        'totalrecipients'))->render();
        
        return response()->json(['success' => true, 'html' => $html]);
    }


    /**
    * Show the form for editing the specified resource.
    * NOTE see addition 'edit' methods below
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $communication = Communication::findOrFail($id);
        $lists = [0=>'Everyone'];
        if ($communication->list && ( $communication->list()->userSavedSearch(false)->count() )) {
            $lists = $communication->list()->pluck('name','id')->toArray();
        } else {
            $lists += Lists::orderBy('name')
            ->legacy()->orWhere(function($query) {
                $query->userSavedSearch();
            })
            ->pluck('name','id')->toArray();
        }

        $content_templates = StatementTemplate::all()->map(function ($template) {
            // we need this encoded version to change tempalte preview from desktop to mobile
            $template->content_html_encoded = htmlentities($template->content);
            return $template;
        });

        // Using this since $communication->documents does not seem to work
        $attachments = Document::where('relation_id', $id)->where('relation_type', get_class($communication))->get();
        
        if (array_get($communication, 'time_scheduled') && strtotime(array_get($communication, 'time_scheduled')) > strtotime(date('Y-m-d H:i:s'))) {
            $totalEmailsScheduled = $communication->sentEmails()->where('sent', 0)->count();
        } else {
            $totalEmailsScheduled = 0;
        }
        
        return view('communications.edit')->with(compact('lists', 'communication', 'content_templates', 'attachments', 'totalEmailsScheduled'));
    }


    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        if ($request->get('print_editor_type') === 'topol') {
            return redirect()->back()->with(['error' => 'Print does not work with the drag and drop editor, please use the simple editor instead.']);
        }
        
        // $communication = $request->session()->get('communication');
        $communication = Communication::find($id);
        $communication = mapModel($communication, $request->get('communication', $request->all())); // get communication from request or assume entire request is communication
        if (!$communication->list_id) $communication->list_id = null;
        $communication->relation_id = $communication->list_id;
        $communication->relation_type = Lists::class;
        $communication->timezone = session('timezone');

        $this->updateTagFields($communication, $request);

        $communication->save();
        $redirect_route_name = $this->getConfigureRoute($request);
        if ($redirect_route_name) return redirect(route($redirect_route_name,$id));
        else { return $communication; }
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $communication = Communication::findOrFail($id);
        Communication::destroy($id);

        return response()->json(['success' => true]);
    }



    /** METHODS FOR ADDITIONAL ROUTES **/


    /**
    * handler for communications.configureprint
    * @param  Request $request
    * @param  [type]  $id
    * @return \Illuminate\Http\Response
    */
    public function configurePrint(Request $request, $id, $tab = null)
    {
        if (!strlen(strip_tags(Communication::find($id)->print_content))) return redirect(route('communications.edit',$id))->with('empty_content','Empty Print Content');
        return $this->configure($request, $id, 'print', $tab);
    }


    /**
    * handler for communications.configureemail
    * @param  Request $request
    * @param  [type]  $id
    * @return \Illuminate\Http\Response
    */
    public function configureEmail(Request $request, $id, $tab = null)
    {
        if (!strlen(strip_tags(Communication::find($id)->content))) return redirect(route('communications.edit',$id))->with('empty_content','Empty Email Content');
        return $this->configure($request, $id, 'email', $tab);
    }


    /**
    * handler for communications.emailsummary
    * @param  Request $request
    * @param  [type]  $id
    * @return [type]
    */
    public function emailSummary($id, $stat = null)
    {
        $statuses = [
            'Queued',
            'sent',
            'delivered',
            'opened',
            'clicked',
            'unsubscribed',
            'complained',
            'error',
            'failed',
            'rejected',
        ];
        $email = Communication::findOrFail($id);
        $list = $email->getRelationTypeInstance;
        $sentOut = $email->sent()->orderBy('id', 'desc');
        
        if ($stat) {
            switch ($stat) {
                case 'sent':
                    $sentOut = $sentOut->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'unsubscribed', 'complained', 'error', 'failed', 'rejected']);
                    break;
                case 'delivered':
                    $sentOut = $sentOut->whereIn('status', ['delivered', 'opened', 'clicked', 'unsubscribed', 'complained']);
                    break;
                case 'opened':
                    $sentOut = $sentOut->whereIn('status', ['opened', 'clicked', 'unsubscribed', 'complained']);
                    break;
                default :
                    $sentOut = $sentOut->where('status', $stat);
                    break;
            }
        }
        
        $status_filter = $stat;
        $sentOut = $sentOut->paginate();
        $totalcontacts = $email->sent()->distinct()->count('contact_id');
        $emails_sent = $email->sent()->get(['id', 'status']);
        $statusTotal = new Collection();
        foreach ($statuses as $status) {
            $statCount = $emails_sent->where('status', $status)->count();
            $statusTotal = $statusTotal->merge([$status => ['total' => $statCount, 'status' => $status]]);
        }
        $data = [
            'list' => $list,
            'email' => $email,
            'total' => $email->sent()->get()->count(),
            'sentOut' => $sentOut,
            'status' => $status_filter,
            'statusTotal' => $statusTotal,
            'totalcontacts' => $totalcontacts,
            'totalCount' => $statusTotal->sum('total')
        ];

        return view('communications.emailsummary')->with($data);
    }


    public function emailTrackHistory($emailId, $sentId, Request $request) {
        $email = Communication::findOrFail($emailId);
        $list = $email->list;
        $sent = $email->sentEmails()->find($sentId);

        $data = [
            'list' => $list,
            'email' => $email,
            'sent' => $sent
        ];

        return view('lists.emails.track-history')->with($data);
    }

    /**
    * handler for communications.printsummary
    * @param  Request $request
    * @param  [type]  $id
    * @return [type]
    */
    public function printSummary($id)
    {
        $communication = Communication::findOrFail($id);
        $printed = $communication->printRecipients()->orderBy('communication_contact.updated_at', 'desc')->paginate();
        // $total = $communication->printRecipients()->with('getMailingAddress')->count();
        $total = $communication->printRecipients()->count();
        $totalcontacts = $communication->printRecipients()->distinct()->count('contact_id');


        return view('communications.printsummary', compact(
             'communication', 'printed', 'total', 'totalcontacts'
        ));
    }



    /** HELPER ROUTINES **/
    /**
    * Helps generalize handling communications.configure.* (various sub-components of configure page)
    * @param  Request $request
    * @param  [integer]  $id                  id of communication
    * @param  [string]  $communication_method print or email
    * @param  [string]  $method_tab           Optional. e.g., configure, confirm, summary
    * @return \Illuminate\Http\Response
    */
    protected function configure(Request $request, $id, $communication_method, $method_tab = null)
    {
        $communication = Communication::find($id);
        $communication->load(
            'lists.inTags.folder','lists.notInTags.folder',
            'includeTags.folder','excludeTags.folder', // needed for email
            'printIncludeTags.folder','printExcludeTags.folder', // needed for print
            'transactionTags.folder','excludedTransactionTags.folder' // needed for confirmations
        );
        $currentTab = $method_tab
        ?  "$method_tab-$communication_method" : $communication_method;

        if (array_get($communication, 'time_scheduled') && strtotime(array_get($communication, 'time_scheduled')) > strtotime(date('Y-m-d H:i:s'))) {
            $totalEmailsScheduled = $communication->sentEmails()->where('sent', 0)->count();
        } else {
            $totalEmailsScheduled = 0;
        }
        
        return view('communications.configure',
        compact('communication','currentTab', 'totalEmailsScheduled'));
    }


    /**
    * Sync various attributes that relate to tags leveraging the Communication model's mutators
    * @param  [Communication] $communication
    * @param  [Request] $request
    */
    protected function updateTagFields(Communication $communication, $request) {
        foreach ([ 'include_tags','exclude_tags',
        'print_include_tags','print_exclude_tags',
        'transaction_tags',
        'excluded_transaction_tags' ] as $tagfield) {
            if ($request->has($tagfield)) {
                $communication->$tagfield = $request->input($tagfield);
            }
        }
    }

    /**
     * Determines which route name to use based on input
     * @param  [Request] $request
     */
    protected function getConfigureRoute($request) {
        if ($request->get('action') === 'email') {
            return 'communications.configureemail';
        } elseif ($request->get('action') === 'print') {
            return 'communications.configureprint';
        } elseif ($request->get('action') === 'save') {
            return 'communications.edit';
        } else {
            return false;
        }
    }

    /**
     * Not used if stripo is not being used as editor
     *
     * @param Request $request
     * @return type
     */
    public function getStripoAuthToken(Request $request)
    {
        $stripo = new \App\Classes\Stripo\StripoAPI();
        return $stripo->getToken();
    }

    public function getTinyJwt()
    {
        $tiny = new Tiny();
        return $tiny->getToken();
    }

    public function publicView($uuid)
    {
        $communication = Communication::withoutGlobalScopes()->with('tenant')->where('uuid', $uuid)->firstOrFail();

        $data = $this->prepareEmailData($communication, 'publicView');

        if (array_get($communication, 'email_editor_type', 'tiny') === 'topol') {
            return view('emails.send.general-topol', $data);
        } else {
            return view('emails.send.general', $data);
        }
    }

    public function downloadTestPdf(Request $request)
    {
        $statement = collect([]);
        $statement->include_transactions = $request->get('include_transactions');
        $statement->transaction_start_date = $request->get('transaction_start_date');
        $statement->transaction_end_date = $request->get('transaction_end_date');
        $statement->print_content = $request->get('print_content');

        $contacts = Contact::whereIn('id', $request->get('contact_ids'))->get();

        if ($statement->include_transactions) {
            foreach ($contacts as $contact) {
                appendTransactionsToContact($contact, $statement->transaction_start_date, $statement->transaction_end_date);
            }
        }

        $data = [
            'statement' => $statement,
            'contacts' => $contacts
        ];

        $filename = 'Test.pdf';
        $pdf = PDF::loadView('contributions.statements.pdf', $data);

        return $pdf->download($filename);
    }
    
    public function cancelSend($id, Request $request) 
    {
        $communication = Communication::findOrFail($id);
        
        $communication->sentEmails()->where('sent', 0)->delete();
        
        return $this->show($id, $request);
    }
}
