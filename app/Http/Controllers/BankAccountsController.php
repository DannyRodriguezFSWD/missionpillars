<?php

namespace App\Http\Controllers;

use App\Classes\MissionPillarsLog;
use App\Models\BankTransactionsAutofill;
use App\Traits\AmountTrait;
use Carbon\Carbon;
use App\Models\Fund;
use App\Classes\MpWrapper\RequestClient as Client;
use App\Models\Account;
use App\Models\Register;
use App\Models\BankAccount;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\RegisterSplit;
use App\Models\BankInstitution;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Classes\Accounting;
use App\Constants;
use App\MPLog;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BankAccountsController extends Controller
{
    use AmountTrait;

    const PERMISSION = 'accounting-bank-integration';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }



    /** Resource methods - except create, edit and show **/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('accounting-view')) abort(403);
        $config = config('plaid');

        $bankInstitutions = BankInstitution::with(['accounts', 'accounts.account'])->get();
        
        $bankAccounts = BankAccount::with('account')
                ->orderByRaw('isnull(account_id)')
                ->orderByRaw('(select bank_institution from bank_institutions where bank_institutions.id = bank_accounts.bank_institution_id)')
                ->orderBy('name')
                ->orderBy('mask')
                ->get();
        
        $funds = Fund::all();
        $unlinkedTransactions = [];
        $linkedRegisterAmounts = [];
        foreach($bankInstitutions as $institution) {
            foreach($institution->accounts as $bank_account) {
                $unlinkedTransactions[$bank_account->id] = $bank_account->bankTransaction()->unmapped()->count();
                // TODO if bringing this back, ensure this is accurate with journal entries see https://app.asana.com/0/728724244905718/1168653110292747/f
                // $startingbalancetotal = $bank_account->account_id ? $bank_account->account->startingBalance()->sum('balance') : 0;
                // $transactionstotal = $bank_account->account_id ? $bank_account->account->transactions()->sum('amount') : 0;
                // $linkedRegisterAmounts[$bank_account->id] = $startingbalancetotal + $transactionstotal;
            }
        }

        $unlinkedTransactions = json_encode($unlinkedTransactions);
        $linkedRegisterAmounts = json_encode($linkedRegisterAmounts);

        $permissions = array_get(auth()->user()->ability([],[
            'accounting-update',
            'accounting-delete',
        ],['return_type'=>'array']),'permissions');

        $bankAccountsCount = BankAccount::count();
        $bankInstitutions = $bankInstitutions->toArray();

        usort($bankInstitutions, function ($a, $b) {
            return $a['bank_institution_id'] == '_import' ? -1 : 1;
        });

        $bankInstitutions = collect($bankInstitutions);
        return view('bank_accounts.index',
        compact( 'config', 'bankInstitutions', 'groups', 'funds',
            'unlinkedTransactions', 'linkedRegisterAmounts', 'permissions', 'bankAccountsCount', 'bankAccounts' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }



    /** Additional route handler methods **/

    /**
     * Link bank accounts to accounts in our software
     */
    public function linkAccount(Request $request)
    {
        $acc = array_get($request, 'account');
        $link = array_get($request, 'link');
        $account = BankAccount::where('id', $acc['id'])->first();

        array_set($account, 'account_id', array_get($link, 'account_id'));
        array_set($account, 'start_date', array_get($request, 'start_date'));
        $account->save();
        $downloaded_transactions = $this->downloadBankTransactions([$account]);

        $bi = BankInstitution::with(['accounts', 'accounts.account'])->get();
        $unlinkedTransactions = [];

        foreach($bi as $institution) {
            foreach($institution->accounts as $accounts) {
                $num = BankTransaction::where('bank_account_id', '=', $accounts->id)->where('mapped', 0)->count();
                $unlinkedTransactions[$accounts->id] = $num;
            }
        }
        
        $bankAccounts = BankAccount::with('account')
                ->orderByRaw('isnull(account_id)')
                ->orderByRaw('(select bank_institution from bank_institutions where bank_institutions.id = bank_accounts.bank_institution_id)')
                ->orderBy('name')
                ->orderBy('mask')
                ->get();
        
        $selected = 0;
        
        for ($i=0; $i<$bankAccounts->count(); $i++) {
            if (array_get($bankAccounts, $i.'.id') == array_get($account, 'id')) {
                $selected = $i;
                break;
            }
        }

        $data = [
            'accounts' => $bankAccounts,
            'institutions' => $bi,
            'unlinked_transactions' => $unlinkedTransactions,
            'downloaded_transactions' => $downloaded_transactions,
            'selected' => $selected,
            'toPage' => (int)ceil(($selected + 1) / 3) - 1
        ];
        
        return response()->json($data);
    }

    public function getAccountTransactions(Request $request)
    {
        $tenant_id = array_get(auth()->user(), 'tenant_id');
        $builder = BankTransaction::with(['accounts', 'accounts.account'])
            ->whereHas('accounts', function($q){
                $q->whereHas('institution', function($q){
                    $q->whereNull('deleted_at');
                });
            })
            ->select([
                'bank_transactions.*',
                'accounts.name as account',
                'accounts.id as account_id',
                'tenants.email as payee',
                'tenants.id as contact_id',
                'funds.id as fund_id',
                'funds.name as fund_name',
                'bta.contact_id'
            ])
            ->leftJoin(DB::raw("(SELECT * FROM bank_transactions_autofill WHERE tenant_id = $tenant_id) AS bta"), function($join){
                $join->on('bank_transactions.description',
                "LIKE", DB::raw('CONCAT(bta.short_description, \'%\')'));
            })
            ->leftJoin('accounts', 'accounts.id', '=', 'bta.account_id')
            ->leftJoin('contacts', 'contacts.id', '=', 'bta.contact_id')
            ->leftJoin('funds', 'funds.id', '=', 'bta.fund_id')
            ->leftJoin('tenants', 'tenants.id', '=', 'bta.tenant_id')
            ->where('bank_transactions.bank_account_id', $request->input('acc_id'))
            ->unmapped()
            ->whereNull('register_id');

        if(filter_var($request->show_pending,FILTER_VALIDATE_BOOLEAN)) $builder->orderBy('pending','desc');
        if(!filter_var($request->show_pending,FILTER_VALIDATE_BOOLEAN)) $builder->where('pending',0);

        if($request->input('sort')) {
            $sort = explode('|', $request->input('sort'));
            if(count($sort) > 1){
                $builder->orderBy($sort[0], $sort[1])->orderBy('id', 'desc');
            }
        }
        $transactions = $builder->paginate();

        return $transactions;
    }

    public function mapAccountTransactions(Request $request)
    {
        $transaction = $request->input('transaction');
        $register = $request->input('registers');
        $reg_account_id = BankAccount::where('id', $transaction['bank_account_id'])->value('account_id');
        $split = array();
        $register_acc = Crypt::decrypt($register['account_id']);
        $register_fund = Crypt::decrypt($register['fund_id']);
        $register['account_register_id'] = $reg_account_id;
        $register['contact_id'] = Crypt::decrypt($register['contact_id']);
        $register['account_id'] = null;
        $register['tenant_id'] = $transaction['tenant_id'];
        if ($register['comment'] == '') {
            $register['comment'] = $transaction['description'];
        }
        $register['register_type'] = 'Bank Transaction';
        $register['bank_transaction_id'] = $transaction['id'];
        $register['fund_id'] = null;

        $bankAccount = BankAccount::where('id', $transaction['bank_account_id'])->value('account_id');

        $reg = new Register;
        $reg = mapModel(new Register, $register);
        $reg->save();

        $split['register_id'] = $reg->id;
        $split['fund_id'] = $register_fund;
        $split['tenant_id'] = $reg->tenant_id;
        $split['amount'] = $reg->amount;
        $split['comment'] = $reg->comment;
        $split['account_id'] = $register_acc;
        $s = mapModel(new RegisterSplit, $split);
        $s->save();

        $trans = BankTransaction::where('id', $transaction['id'])->first();
        $trans->mapped = true;
        $trans->save();

        return response('success', 200);

    }
    public function mapAccountTransactionsBulk(Request $request)
    {
        dd($request);
    }

    public function syncSingleTransaction(Request $request) {
        $registry_record_type = null;
        $splits_records_type = null;
        $bank_transaction_registry = array_get($request, 'register');
        $registry = array_except(array_get($request, 'register'), [
            'id',
            'created_by',
            'updated_by',
            'created_by_session_id',
            'updated_by_session_id',
            'deleted_at',
            'created_at',
            'updated_at',
            'accounts'
        ]);
        array_set($registry, 'id', array_get($bank_transaction_registry, 'register_id'));
        array_set($registry, 'account_register_id', array_get($bank_transaction_registry, 'accounts.account.id'));

        if(!empty(array_get($registry, 'credit'))){
            $registry_record_type = 'credit';
            $splits_records_type = 'debit';
        }
        else if(!empty(array_get($registry, 'debit'))){
            $registry_record_type = 'debit';
            $splits_records_type = 'credit';
        }

        $request_splits = array_get($request, 'splits', []);
        $register = Accounting::createOrUpdateRegistry($registry, $registry_record_type);
        $splits = Accounting::createOrUpdateSplits($register, $request_splits, $registry_record_type, $splits_records_type);

        $comment = trim(substr(array_get($registry, 'description'), 0, 5));
        $autofill = BankTransactionsAutofill::where('short_description', 'LIKE', $comment."%")->first();

        if(empty($autofill)) {
            $autofill = new BankTransactionsAutofill();
        }

        $split = array_get($request_splits, 0);
        $autofill = mapModel($autofill, $split);
        array_set($autofill, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
        array_set($autofill, 'short_description', $comment);
        // TODO After troubleshooting, re-enable saving autofill rows
        // $autofill->save();

        $bank_transaction = BankTransaction::find(array_get($bank_transaction_registry, 'id'));
        if(is_null($bank_transaction)){
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
            $data = [
                'event' => 'Error linking bank transaction',
                'caller_function'=> $backtrace[0]['class']."::".$backtrace[0]['function'],
                'url'=> url()->current(),
                'data'=>json_encode([ 'backtrace'=>$backtrace ])
            ];
            MissionPillarsLog::log($data);
            abort(500, 'error linking bank transaction');
        }
        array_set($bank_transaction, 'register_id', array_get($register, 'id'));
        array_set($bank_transaction, 'mapped', true);
        $bank_transaction->save();

        return ['success' => true];
    }

    public function syncTransactions(Request $request){
        $acc_id = array_get($request, 'acc_id');
        if ($acc_id) {
            $accounts = BankAccount::with('institution')->where('id', $acc_id)->get();
        } else {
            $accounts = BankAccount::with('institution')->get();
        }

        list($downloaded_transactions, $errors) = $this->downloadBankTransactions($accounts, true);
        $bi = BankInstitution::with('accounts')->get();
        $unlinkedTransactions = [];

        foreach($bi as $institution) {
            foreach($institution->accounts as $accounts) {
                $num = BankTransaction::where('bank_account_id', '=', $accounts->id)->where('mapped', 0)->count();
                $unlinkedTransactions[$accounts->id] = $num;
            }
        }
        //$unlinkedTransactions = json_encode($unlinkedTransactions);
        //return $unlinkedTransactions;
        
        $bankAccounts = BankAccount::with('account')
                ->orderByRaw('isnull(account_id)')
                ->orderByRaw('(select bank_institution from bank_institutions where bank_institutions.id = bank_accounts.bank_institution_id)')
                ->orderBy('name')
                ->orderBy('mask')
                ->get();
        
        $data = [
            'unlinked_transactions' => $unlinkedTransactions,
            'downloaded_transactions' => $downloaded_transactions,
            'errors' => $errors,
            'accounts' => $bankAccounts
        ];
        return response()->json($data);
    }

    public function bankAuthorizationCallback(Request $request){
        //dd($request->all());
        //at least on sandbox if we request new token and try to sync with old accounts,
        //it fails as if there were a relationship token/account_id
        //now i wonder if this would happen at the time we switch from development enviroment
        //to production enviroment, because technically: token would be different
        //transactions id would stay the same
        //example of api call using new token, old account ids:
        //url:https://sandbox.plaid.com/transactions/get
        //Error
        //"error_code": "INVALID_ACCOUNT_ID",
        //"error_message": "One or more Account id(s) missing
        //request:
        //{"endpoint":"https:\/\/sandbox.plaid.com\/transactions\/get","body":{"client_id":"5cc309605e3f460012a6825d","secret":"b7a308648d89cf07f216ba8b4e8fd9","access_token":"access-sandbox-0e454b4d-37ce-4fe6-99be-aee2a3a80290","start_date":"2018-08-05","end_date":"2019-08-05","options":{"account_ids":["AeZWDa4D4QsVPjA9ebweH8agVy9jnrC1peWkV"]}}}

        //example of access tokens:
        //sandbox: access-sandbox-e90211b9-fa64-4b20-8681-fa995ef32b7a
        //development: access-development-4861f802-df4c-4e0a-ba36-123456789098
        //im assuming the productions would look like: access-production-e90211b9-fa64-4b20-8681-fa995ef32b7a

        $bank_institution_id = array_get($request, 'metadata.institution.institution_id');
        $bank_institution_name = array_get($request, 'metadata.institution.name');
        $bank_institution = BankInstitution::withTrashed()->where('bank_institution_id', $bank_institution_id)->first();

        if (is_null($bank_institution)) {
            $access_token = $this->getAccessToken($request);
            //previously, we were getting bank info and storing id, institution name,
            //owner name and token in bank_institutions table, because we are not going to access
            //to identity endpoint by now, we are going to create a record from metadata to store token
            $bank_institution = new BankInstitution();
            array_set($bank_institution, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
            array_set($bank_institution, 'token', $access_token);
            array_set($bank_institution, 'bank_institution_id', $bank_institution_id);
            array_set($bank_institution, 'bank_institution', $bank_institution_name);
            $bank_institution->save();
        } else {//we have already a token for current institutions, we need to restore it
            if ($bank_institution->trashed()) {
                $bank_institution->restore();
            }

            // we refresh the access token
            $access_token = $this->getAccessToken($request);
            $bank_institution->token = $access_token;
            $bank_institution->save();
        }

        if(!empty($access_token)){
            return $this->getBankAccounts($access_token, array_get($bank_institution, 'id'));
        }

        return response()->json(['success' => true], 200);
    }

    public function getAccessToken(Request $request){
        $config = config('plaid');
        $publicToken = array_get($request, 'public_token');
        //Exchange Tokens: we exchange public token for access token
        //access_token is required to make api calls
        $body = [
            'client_id' => array_get($config, 'plaid_client_id'),
            'secret' => array_get($config, 'plaid_secret'),
            'public_token' => $publicToken
        ];
        $result = $this->postAction('/item/public_token/exchange', $body);
        $response = json_decode($result, true);

        //the key for api calls
        $access_token = array_get($response, 'access_token');
        return $access_token;
    }

    public function getBankAccounts($access_token, $bank_institution_id = null){
        $config = config('plaid');
        //prevously, the identity object had the accounts data, now we need to get it from the accounts endpoint
        $body = [
            'client_id' => array_get($config, 'plaid_client_id'),
            'secret' => array_get($config, 'plaid_secret'),
            'access_token' => $access_token
        ];
        $result = $this->postAction('/accounts/get', $body);

        $response = json_decode($result, true);

        if (empty(array_get($response, 'error_code'))) {
            $item_institution_id = array_get($response, 'item.institution_id');

            $bankAccounts = array_get($response, 'accounts', []);

            if (empty($bankAccounts)) {
                return response()->json([
                    'error' => true,
                    'error_code' => 'NO_ACCOUNTS'
                ], 200);
            } else {
                foreach (array_get($response, 'accounts', []) as $account) {
                    $bank_account_id = array_get($account, 'account_id');
                    //from https://plaid.com/docs/#accounts
                    //The unique ID of the account. Note: In some instances, account IDs may change.
                    //so i think we can not completely relly on account ID, at least on sandbox th ID is alwasy different
                    //so we check the official name as well hoping institution doesn't rename it
                    //official_name  String, nullable	The official name of the Account as given by the financial institution.
                    $bank_account = BankAccount::whereHas('institution', function($q) use($item_institution_id){
                        $q->where('bank_institution_id', $item_institution_id);
                    })->where('bank_account_id', $bank_account_id)
                    // ->where('mask', array_get($account, 'mask'))
                    ->first();

                    /*
                    if(is_null($bank_account)){
                        $bank_account = BankAccount::whereHas('institution', function($q) use($item_institution_id){
                                            $q->where('bank_institution_id', $item_institution_id);
                                        })->where([
                                            ['official_name', '=', array_get($account, 'official_name')],
                                            ['account_type', '=', array_get($account, 'type')],
                                            ['account_subtype', '=', array_get($account, 'subtype')],
                                        ])->first();
                    }
                    */

                    //we only add account if doesn't exist already
                    if(is_null($bank_account)){
                        $bank_account = new BankAccount();
                    } else {
                        if ($bank_account->trashed()) $bank_account->restore();
                    }
                    //if balances are updated in plaid, and account balances are the data we want, we can update it here
                    array_set($bank_account, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
                    array_set($bank_account, 'bank_institution_id', $bank_institution_id);
                    array_set($bank_account, 'bank_account_id', array_get($account, 'account_id'));
                    array_set($bank_account, 'iso_currency_code', array_get($account, 'balances.iso_currency_code'));
                    array_set($bank_account, 'mask', array_get($account, 'mask'));
                    array_set($bank_account, 'name', array_get($account, 'name'));
                    array_set($bank_account, 'official_name', array_get($account, 'official_name'));
                    array_set($bank_account, 'account_type', array_get($account, 'type'));
                    array_set($bank_account, 'account_subtype', array_get($account, 'subtype'));
                    array_set($bank_account, 'current_balance', array_get($account, 'balances.current'));
                    array_set($bank_account, 'available_balance', array_get($account, 'balances.available'));
                    array_set($bank_account, 'limit_balance', array_get($account, 'balances.limit'));
                    $bank_account->save();

                }

                return response()->json(['success' => true], 200);
            }
        } else {
            return response()->json([
                'error' => true,
                'error_code' => array_get($response, 'error_code'),
                'error_message' => array_get($response, 'error_message')
            ], 200);
        }
    }

    public function getBankData(Request $request) {
        $transaction = BankTransaction::find(array_get($request, 'bank_transaction_id'));
        $amount = array_get($request, 'amount', 0);
        $bank = BankAccount::find(array_get($transaction, 'bank_account_id'));
        $account = array_get($bank, 'account');

        // if null thant it is importet we don't need to invert
        if (is_null(array_get($transaction, 'transaction_id'))) {
            if($amount < 0) {
                $account->credit = abs($amount);
            } else {
                $account->debit = abs($amount);
            }
        } else {
            /**
             * https://support.plaid.com/hc/en-us/articles/360008413653-Negative-transaction-amount
             * A transaction with a negative amount represents money flowing into the account, such as a direct deposit. A transaction with a positive amount represents money flowing out of the account, such as a purchase. For more information about the data fields returned for accounts and transactions, please see the data overview.
             */
            if($amount > 0) {
                $account->credit = abs($amount);
            } else {
                $account->debit = abs($amount);
            }
        }
        
        return response()->json($account);
    }

    /**
     * Download bank transactions from Plaid
     * 
     * @param string $accessToken - Plaid bank institution access token
     * @param string $account_id - Id of the bank account in Plaid
     * @param date $start_date - Date of the earliest transaction to get
     * @param int $count - Total number of transactions to get minimum = 1. maximum = 500
     * @param int $offset - Number of transactions to skip
     * @return type
     */
    public function getTransactions($accessToken = null, $account_id = null, $start_date = null, $count = 100, $offset = 0)
    {
        $config = config('plaid');
        $end_date = Carbon::now()->toDateString();
        if (empty($start_date)) {
            $start_date = Carbon::now()->subYear()->toDateString();
        }
        $body = [
            'client_id' => $config['plaid_client_id'],
            'secret' => $config['plaid_secret'],
            'access_token' => $accessToken,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'options' => [
                'account_ids' => $account_id,
                'count' => $count,
                'offset' => $offset
            ]

        ];

        $result = json_decode($this->postAction('/transactions/get', $body), true);

        return $result;
    }

    public function postAction($endPoint, $body)
    {
        $logeverything = false;
        $config = config('plaid');
        $uri = $config['plaid_env_url'][$config['plaid_env']].$endPoint;
        $client = new Client([
            'base_uri' => $config['plaid_env_url'][$config['plaid_env']]
        ]);

        try {
            $response = $client->post($uri, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($body)
            ]);

            if($logeverything || $config['plaid_env'] != 'production'){
                MPLog::create([
                    'event' => 'PLAID',
                    'url' => $uri,
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'request' => json_encode($body),
                    'response' => $response->getBody()
                ]);
            }

            return $response->getBody();
        } catch (\Throwable $th) {
            MPLog::create([
                'event' => 'PLAID',
                'url' => $uri,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $th->getCode(),
                'message' => $th->getMessage(),
                'data' => json_encode($th),
                'request' => json_encode(['endpoint' => $uri, 'body' => $body])
            ]);

            $response = json_decode($th->getResponse()->getBody()->getContents(), true);
            return json_encode($response);
        } catch(ClientException $ex){
            MPLog::create([
                'event' => 'PLAID',
                'url' => $uri,
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'data' => json_encode([
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                    'request' => $ex->getRequest(),
                    'response' =>  $ex->getResponse()
                ]),
                'request' => json_encode(['endpoint' => $uri, 'body' => $body])
            ]);
        }

    }

    public function getAction($endPoint, $body)
    {
        $config = config('plaid');

        $client = new Client([
            'base_uri' => $config['plaid_env_url'][$config['plaid_env']]
        ]);

        $response = $client->get($endPoint, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body)
        ]);

        return $response->getBody();
    }

    public function setAccessToken(Request $request)
    {
        $accessToken = $request->input('access_token');
    }

    public function downloadBankTransactions($accounts, $witherrors = false){
        $downloaded_records = 0;
        $errors = [];
        foreach($accounts as $account) {
            $token = array_get($account, 'institution.token');
            $bank_account_id = array_get($account, 'bank_account_id');
            $bank_institution_id = array_get($account, 'institution.id');
            //if for some reason token or bank_account_id are null,
            //we avoid error that will stop execution
            if(is_null($token) || is_null($bank_account_id)){
                continue;
            }

            // NOTE Don't think this should be called each time - see https://app.asana.com/0/728724244905718/1145176216835363/f
            // $this->getBankAccounts($token, $bank_institution_id);

            // get the last transaction date
            $transaction_date = BankTransaction::select('date')->where('bank_account_id', array_get($account, 'id'))
                ->orderBy('date', 'desc')->pluck('date')->first();

            // get the earliest pending transaction date
            // we are only looking for pendings of the last 5 days so in case we have older pendings we don't download all the transactions
            $lastPendingTransactionDate = BankTransaction::select('date')->where('bank_account_id', array_get($account, 'id'))
                ->where('pending', 1)->whereRaw('date >= ? - INTERVAL 5 DAY', [$transaction_date])->orderBy('date', 'asc')->pluck('date')->first();

            // if there is a pending transaction we want to download all transaction from that date
            if (!empty($lastPendingTransactionDate)) {
                $transaction_date = $lastPendingTransactionDate;
            }

            if (empty($transaction_date)) {
                $transaction_date = array_get($account, 'start_date');
            }
            
            // since Plaid is paginating we first check the total count
            $transactions = $this->getTransactions($token, [$bank_account_id], $transaction_date, 1, 0);
            
            // use this to test errors
            //$transactions = json_decode('{"display_message":null,"documentation_url":"https://plaid.com/docs/?ref=error#item-errors","error_code":"PRODUCT_NOT_READY","error_message":"the requested product is not yet ready. please provide a webhook or try the request again later","error_type":"ITEM_ERROR","request_id":"94Wt5RY3bitgsjq","suggested_action":null}', true);
            //$transactions = json_decode('{"display_message":null,"error_code":"ITEM_LOGIN_REQUIRED","error_message":"the login details of this item have changed (credentials, MFA, or required user action) and a user login is required to update this information. use Link\'s update mode to restore the item to a good state", "error_type":"ITEM_ERROR","request_id":"81Rt1k2lyNcxMif","suggested_action":null}', true);
            
            if (isset($transactions['error_code'])) {
                // general error handling
                $error_code = $transactions['error_code'];
                $error_message = $transactions['error_message'];
                $error = compact('error_code','error_message');

                array_set($account, 'plaid_error_code', $error_code);
                array_set($account, 'plaid_error_message', $error_message);
                $account->save();
                
                // TODO Do not add any additional error specific information here, consider moving this handling below
                if ($transactions['error_code'] == 'ITEM_LOGIN_REQUIRED') {
                    $error = array_merge($transactions, $error, [
                        'bank'    => array_get($account, 'institution.bank_institution'),
                        'bank_id' => $bank_institution_id,
                    ]);
                    // One of the bank accounts requires a new login, ...
                    if (!$witherrors) return $error; // ... stop and return error
                    // ... or just continue
                }

                if ($witherrors) {
                    $errors[$account->id] = $error;
                    // TODO add any additional specific information needed for errors here
                }
            } else {
                array_set($account, 'plaid_error_code', null);
                array_set($account, 'plaid_error_message', null);
                $account->save();
                
                $totalCount = array_get($transactions, 'total_transactions');
            
                if ($totalCount > 0) {

                    for ($i = 0; $i < $totalCount; $i+=100) {
                        $transactions = $this->getTransactions($token, [$bank_account_id], $transaction_date, 100, $i);

                        foreach (array_get($transactions, 'transactions', []) as $t) {
                            // first we check if we already have a transaction with the same transaction_id
                            $transaction = BankTransaction::whereRaw('BINARY transaction_id = ?', [array_get($t, 'transaction_id')])->first();

                            // if we don't than we check if we have a transaction that has transaction_id that matches with the new transaction pending_transaction_id
                            if (is_null($transaction)) {
                                $transaction = BankTransaction::whereRaw('BINARY transaction_id = ?', [array_get($t, 'pending_transaction_id')])->first();
                            }

                            // else just make a new transaction
                            if (is_null($transaction)) {
                                $transaction = new BankTransaction;
                                $downloaded_records++;
                            }

                            array_set($transaction, 'tenant_id', array_get($account, 'tenant_id'));
                            array_set($transaction, 'bank_institution_id', $bank_institution_id);
                            array_set($transaction, 'bank_account_id', array_get($account, 'id'));
                            array_set($transaction, 'transaction_id', array_get($t, 'transaction_id'));
                            array_set($transaction, 'transaction_type', array_get($t, 'transaction_type'));
                            array_set($transaction, 'amount', array_get($t, 'amount'));
                            array_set($transaction, 'category_id', array_get($t, 'category_id'));
                            array_set($transaction, 'date', array_get($t, 'date'));
                            array_set($transaction, 'description', str_limit(array_get($t, 'name'),180));
                            array_set($transaction, 'payee', array_get($t, 'payment_meta.payee'));
                            array_set($transaction, 'payer', array_get($t, 'payment_meta.payer'));
                            array_set($transaction, 'payment_method', array_get($t, 'payment_meta.payment_method'));
                            array_set($transaction, 'payment_processor', array_get($t, 'payment_meta.payment_processor'));
                            array_set($transaction, 'ppd_id', array_get($t, 'payment_meta.ppd_id'));
                            array_set($transaction, 'reason', array_get($t, 'payment_meta.reason'));
                            array_set($transaction, 'reference_number', array_get($t, 'payment_meta.reference_number'));
                            array_set($transaction, 'pending', array_get($t, 'pending'));
                            array_set($transaction, 'pending_transaction_id', array_get($t, 'pending_transaction_id'));
                            try {
                                $transaction->save();
                            } catch (\Exception $e) {
                                MissionPillarsLog::exception($e, json_encode(compact('t','transaction')));
                            }
                        }
                    }
                }
            }
        }
        
        return $witherrors ? [$downloaded_records,$errors] : $downloaded_records;
    }

    /**
     * (Soft) deletes ALL bank instituations for the current tenant and related bank accounts
     * TODO consider implemented cascading soft-deletes here (and probably all over) https://laravel-news.com/cascading-soft-deletes
     * @param  Request $request
     */
    public function stopSyncTransactions(Request $request){
        $bank_account_id = $request->get('bank_account_id');
        if ($bank_account_id) {
            BankAccount::where('id', $bank_account_id)->delete();
            return;
        }

        // otherwise delete all
        $institutions = BankInstitution::all();
        foreach ($institutions as $institution) {
            $institution->accounts()->delete();
            $institution->delete();//soft deleted
        }
    }

    /**
     * Unlinks the specified bank account from the register it is linked to
     * @param  Request $request
     */
    public function unlinkRegister(Request $request) {
        $bank_account_id = $request->get('bank_account_id');
        $bank_account = BankAccount::find($bank_account_id);
        $bank_account->account_id = null;
        $bank_account->save();
    }


    public function getRegisterGroups() {
        $groups = AccountGroup::with(['accounts'=> function($query) {
            $query->registers();
            $query->unlinked();
        }])->orderBy('name', 'asc')->get();
        return response()->json($groups);
    }

    public function preview()
    {
        $transactions = request()->transactions;
        if (request()->has_header) array_shift($transactions);
        $errors = [];
        $transactions2 = [];
        foreach ($transactions as $key => $transaction) {
            $tran['id'] = $key;
            $tran['description'] = $transaction[request()->columns['description_column']];
            $tran['amount'] = $transaction[request()->columns['amount_column']];
            $tran['date'] = $transaction[request()->columns['date_column']];
            $validator = Validator::make($tran, [
                'description' => 'string|nullable',
                'amount' => 'numeric',
                'date' => 'required',
            ]);
            if ($validator->fails()) {
                array_push($errors, [
                    'row_number' => request()->has_header ? ($key + 2) : ($key + 1),
                    'messages' => join(', ', $validator->errors()->all()),
                ]);
            } else {
                $dateFormat = request()->date_format;
                $date_arr = date_parse_from_format($dateFormat, $tran['date']);
                if (checkdate($date_arr['month'],$date_arr['day'],$date_arr['year'])) {
                    $tran['date'] = Carbon::parse(join('-',array_splice($date_arr, 0, 3)))->format($dateFormat);
                } else {
                    $formats = [
                        'Y-m-d' => 'YYYY-MM-DD or YYYY/MM/DD',
                        'm-d-Y' => 'MM-DD-YYYY or MM/DD/YYYY',
                        'd-m-Y' => 'DD-MM-YYYY or DD/MM/YYYY',
                        'Y-d-m' => 'YYYY-DD-MM or YYYY/DD/MM',
                    ];
                    array_push($errors, [
                        'row_number' => request()->has_header ? ($key + 2) : ($key + 1),
                        'messages' => 'Invalid Date ' . $tran['date'] . ' on ' . $formats[$dateFormat] . ' format.',
                    ]);
                }
            }

            array_push($transactions2,$tran);
        }

        return $errors ? response($errors, '400') : $transactions2;
    }

    public function parseImport()
    {
        $validateFile = Validator::make(
            ['file' => request()->file, 'extension' => strtolower(request()->file->getClientOriginalExtension()),],
            ['file' => 'required', 'extension' => 'required|in:csv,xlsx,xls',]);

        if ($validateFile->fails()) return response('Invalid file format, only csv, xlsx, xls are allowed', 400);

        try {
            config(['excel.import.heading' => false]);
            config(['excel.import.dates.enabled' => false]);
            $transactions = Excel::load(request()->file)->get();
            return response(json_encode(['columns' => $transactions[0], 'transactions' => $transactions]));
        } catch (\Exception $exception) {
            return response('Something went wrong', 400);
        }
    }

    public function import_transactions(){
        $transactions = request()->transactions;
        if (count($transactions)) {
            if (request()->bank_institution['bank_institution_id'] == 'new') {
                $bi_id = snake_case(request()->bank_institution['bank_institution'] . request()->bank_institution['id']);
                $Bi = BankInstitution::firstOrCreate(
                    ['bank_institution' => request()->bank_institution['bank_institution'], 'bank_institution_id' => $bi_id],
                    ['tenant_id' => auth()->user()->tenant_id]
                );
            } else {
                $Bi = BankInstitution::find(request()->bank_institution['id']);
            }


            if (request()->bank_account['bank_account_id'] !== 'new'){
                $ba = BankAccount::find(request()->bank_account['id']);
                if (!$ba->account_id) $ba->account_id = request()->bank_account['account_id'];
                $ba->save();
            }else{
                $ba = new BankAccount();
                $ba->name = request()->bank_account['name'];
                $ba->account_id = request()->bank_account['account_id'];
                $ba->account_type = request()->bank_account_type;
                $ba->bank_institution_id = $Bi->id;
                $ba->bank_account_id = snake_case(request()->bank_account['name'] . request()->bank_account['id']);
                $ba->iso_currency_code = 'USD';
                $ba->tenant_id = auth()->user()->tenant_id;
                $ba->imported = 1;
                $ba->save();
            }
            
            $dateFormat = request()->date_format;
            
            foreach ($transactions as $transaction){
                $bt = new BankTransaction();
                $bt->fill([
                    'tenant_id' => auth()->user()->tenant_id,
                    'bank_institution_id' => $Bi->id,
                    'bank_account_id' => $ba->id,
                    'amount' => $transaction['amount'],
                    'date' => Carbon::createFromFormat($dateFormat, $transaction['date'])->format('Y-m-d'),
                    'description' => $transaction['description'],
                    'pending' => 0,
                    'mapped' => 0,
                    'hidden' => 0,
                ]);
                $bt->save();
            }
        }
        $bankInstitutions = BankInstitution::with(['accounts', 'accounts.account'])->get();

        foreach($bankInstitutions as $institution) {
            foreach($institution->accounts as $accounts) {
                $num = BankTransaction::where('bank_account_id', '=', $accounts->id)->where('mapped', 0)->count();
                $unlinkedTransactions[$accounts->id] = $num;
            }
        }

        $bankInstitutions = $bankInstitutions->toArray();

        if (request()->bank_institution['bank_institution_id'] == 'new'){
            usort($bankInstitutions, function ($a, $b) use($Bi) {
                return $a['id'] == $Bi->id ? -1 : 1;
            });
        }

        $bankAccounts = BankAccount::with('account')
                ->orderByRaw('isnull(account_id)')
                ->orderByRaw('(select bank_institution from bank_institutions where bank_institutions.id = bank_accounts.bank_institution_id)')
                ->orderBy('name')
                ->orderBy('mask')
                ->get();
        
        $selected = 0;
        
        for ($i=0; $i<$bankAccounts->count(); $i++) {
            if (array_get($bankAccounts, $i.'.id') == array_get($ba, 'id')) {
                $selected = $i;
                break;
            }
        }
        
        return [
            'institutions' => $bankInstitutions,
            'account_id' => $ba->id,
            'unlinked_transactions' => $unlinkedTransactions,
            'accounts' => $bankAccounts,
            'selected' => $selected,
            'toPage' => (int)ceil(($selected + 1) / 3) - 1
        ];
    }
    
    /*
     * This is used because plaid does not support http for redirect uri's unless it's localhost
     */
    public function getRedirectUri()
    {
        $env = env('APP_ENV', 'local');
        return $env === 'local' ? config('plaid.plaid_redirect_uri') : route('oauth-page');
    }
    
    public function createLinkToken()
    {
        $config = config('plaid');
        $user = new \stdClass();
        $user->client_user_id = (string)auth()->user()->tenant_id;
        
        $body = [
            'client_id' => $config['plaid_client_id'],
            'secret' => $config['plaid_secret'],
            'user' => $user,
            'client_name' => 'Mission Pillars',
            'products' => [$config['plaid_products']],
            'country_codes' => ['US'],
            'language' => 'en',
//            'redirect_uri' => $this->getRedirectUri()
        ];
        
        $response = json_decode($this->postAction('/link/token/create', $body), true);
        $linkToken = array_get($response, 'link_token');
        
        if ($linkToken) {
            return response()->json(['success' => true, 'linkToken' => $linkToken]);
        } else {
            throw new \Exception('Unable to create link token with plaid.');
        }
    }
    
    public function updateLinkToken($id)
    {
        $config = config('plaid');
        $bankInstitution = BankInstitution::findOrfail($id);
        $accessToken = array_get($bankInstitution, 'token');
        $user = new \stdClass();
        $user->client_user_id = (string)auth()->user()->tenant_id;
        
        $body = [
            'client_id' => $config['plaid_client_id'],
            'secret' => $config['plaid_secret'],
            'client_name' => 'Mission Pillars',
            'user' => $user,
            'country_codes' => ['US'],
            'language' => 'en',
            'access_token' => $accessToken,
//            'redirect_uri' => $this->getRedirectUri()
        ];
        
        $response = json_decode($this->postAction('/link/token/create', $body), true);
        $linkToken = array_get($response, 'link_token');
        
        if ($linkToken) {
            return response()->json(['success' => true, 'linkToken' => $linkToken]);
        } else {
            throw new \Exception('Unable to create link token with plaid.');
        }
    }
    
    /**
     * For testing purposes only!
     * Use this to trigger the ITEM_LOGIN_REQUIRED error from plaid
     * Specify the institution ID from bank_institutions table
     * 
     * @param int $id
     * @return string
     */
    public function resetLogin($id)
    {
        abort(404);
        
        $config = config('plaid');
        $bankInstitution = BankInstitution::findOrfail($id);
        $accessToken = array_get($bankInstitution, 'token');
        
        $body = [
            'client_id' => $config['plaid_client_id'],
            'secret' => $config['plaid_secret'],
            'access_token' => $accessToken
        ];
        
        return json_decode($this->postAction('/sandbox/item/reset_login', $body), true);
    }
}
