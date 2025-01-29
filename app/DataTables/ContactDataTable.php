<?php

namespace App\DataTables;

use App\DataTables\Scopes;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\TransactionSplit;
use Yajra\Datatables\Services\DataTable;

class ContactDataTable extends DataTable
{
    const DEFAULT_URI = 'crm/search/contacts';
    /**
     * NOTE: see /public/vendors/datatables/custom-scripts.js
     */
    protected $actions = [
        'print', 'excel', 'csv',
        'updateTags',
        'emailOrPrint',
        'sendSms',
        'getContactIds'
    ];

    protected static $custom_filters = [
        'transaction_date_min',
        'transaction_date_max',
        'transaction_amount_min',
        'transaction_amount_max',
        'transaction_amount_use_sum',
        'transaction_campaigns',
        'transaction_purposes',
        'contact_tags',
        'contact_excluded_tags',
        'transaction_tags',
        'column_blanks',
        'column_no_blanks',
        'event_registration_paid',
        'event_registration_checked_in',
        'event_registration_released_ticket',
        'event_registration',
        'recurring_donors',
        'ex_recurring_donors',
        'non_recurring_donors',
        'latent_not_give_from_date',
        'latent_not_give_to_date',
        'latent_gave_from_date',
        'latent_gave_to_date',
        'groups',
        'primary_contacts',
        'created_at_from',
        'created_at_to',
        'updated_at_from',
        'updated_at_to',
        'contact_type',
        'custom_field',
        'custom_field_value'
    ];

    public $numberColumns = [
        'total_amount', 'lifetime_total', 'last_transaction_amount'
    ];
    
    public $dateColumns = [
        'last_transaction_date', 'created_at', 'updated_at'
    ];
    
    /**
     * Build DataTable class.
     *
     * @return \Yajra\Datatables\Engines\BaseEngine
     */
    public function dataTable()
    {
        // TODO call this from the command line and perhaps filter or make
        // \Log::info($this->query()->toSql());
        $dt = $this->datatables
        ->eloquent($this->query());
        $this->addAddressColumns($dt);
        $this->addTagsColumn($dt);
        
        // only add total amount column if can view transactions
        if (auth()->user()->can('transaction-view')) {
            $this->addTotalAmountColumn($dt);
            $this->addLifetimeTotalColumn($dt);
            $this->addLastTransactionAmountColumn($dt);
            $this->addLastTransactionDateColumn($dt);
        }
        
        $this->addCampaignsColumn($dt);
        $this->addPurposesColumn($dt);
        $this->addPreferredNameColumn($dt);
        $this->addFamilyNameColumn($dt);
        $this->addFamilyEnvelopeNameColumn($dt);
        $this->addMergedNameColumn($dt);
        $this->addCreatedAtColumn($dt);
        $this->addUpdatedAtColumn($dt);
        $this->addNotesColumn($dt);
        $dt->addColumn('link', "<a href='{{ route('contacts.show',\$id) }}' class='btn btn-primary'>View Contact</a>")
        ->rawColumns(['link']);
        
        if (auth()->user()->can('transaction-view')) {
            $this->addTotalSumColumn($dt);
        }
        
        return $dt;
    }

    protected function addAddressColumns($dt) {
        foreach([ 'mailing_address_1', 'mailing_address_2', 'p_o_box', 'city', 'region', 'postal_code'] as $col) {
            $dt->addColumn($col, function (Contact $contact) use ($col) {
                if (array_get($contact, 'addresses')) {
                    $mailingAddress = array_get($contact, 'addresses')->where('is_mailing', true)->first();
                    if ($mailingAddress) {
                        return array_get($mailingAddress, $col);
                    }
                }
                
                return '';
            })
            ->filterColumn($col, function($query, $keyword) use ($col) {
                // modified from $contact->mail $contact->mailingAddress()->toSql()
                $sql =  "exists (select 1 from `addresses` where `addresses`.`relation_id` = `contacts`.`id` and `addresses`.`relation_id` is not null and `addresses`.`relation_type` LIKE '%Contact' and `is_mailing` = 1 and `addresses`.`deleted_at` is null and (`addresses`.`tenant_id` = ? or `addresses`.`tenant_id` is null) and `$col` LIKE '%{$keyword}%')";

                $query->whereRaw($sql,[auth()->user()->tenant_id]);
            });
        }
    }

    protected function addTotalAmountColumn($dt) {
        $dt->addColumn('total_amount', function (Contact $contact) {
            // $total = $contact->transactionSplits()->completed()->get()->sum('amount');
            $total = $contact->transactionSplits->sum('amount');
            return $total ? '$ '.to_currency($total) : '';
        })
        ->filterColumn('total_amount', function ($query, $keyword) {
            // modified from $contact->mail $contact->transactionSplits()->completed()->toSql()
            $sql = "( select SUM(amount) from `transaction_splits` inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` where `transactions`.`deleted_at` is null and `transactions`.`contact_id` = `contacts`.`id` and `status` = 'complete'  and `transaction_splits`.`deleted_at` is null and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null)) LIKE '%{$keyword}%'";

            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        });
    }
    
    protected function addLifetimeTotalColumn($dt) 
    {
        $dt->addColumn('lifetime_total', function (Contact $contact) {
            $total = $contact->transactionSplits()->completed()->get()->sum('amount');
            return $total ? '$ '.to_currency($total) : '';
        })
        ->filterColumn('lifetime_total', function ($query, $keyword) {
            $sql = "( select SUM(amount) from `transaction_splits` inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` where `transactions`.`deleted_at` is null and `transactions`.`contact_id` = `contacts`.`id` and `status` = 'complete'  and `transaction_splits`.`deleted_at` is null and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null)) LIKE '%{$keyword}%'";
            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        });
    }
    
    protected function addLastTransactionAmountColumn($dt) 
    {
        $dt->addColumn('last_transaction_amount', function (Contact $contact) {
            $lastTransaction = $contact->transactions()->completed()->orderBy('transaction_initiated_at', 'desc')->first();
            return $lastTransaction ? '$ '.to_currency(array_get($lastTransaction, 'template.splits')->sum('amount')) : '';
        })
        ->filterColumn('last_transaction_amount', function ($query, $keyword) {
            $sql = "( select amount from `transaction_splits` inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` where `transactions`.`deleted_at` is null and `transactions`.`contact_id` = `contacts`.`id` and `status` = 'complete'  and `transaction_splits`.`deleted_at` is null and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null) ORDER BY `transactions`.`transaction_initiated_at` DESC LIMIT 1) LIKE '%{$keyword}%'";
            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        });
    }
    
    protected function addLastTransactionDateColumn($dt) 
    {
        $dt->addColumn('last_transaction_date', function (Contact $contact) {
            $lastTransaction = $contact->transactions()->completed()->orderBy('transaction_initiated_at', 'desc')->first();
            return $lastTransaction ? date('m/d/Y', strtotime(displayLocalDateTime(array_get($lastTransaction, 'transaction_initiated_at')))) : '';
        })
        ->filterColumn('last_transaction_date', function ($query, $keyword) {
            $keywordEx = explode('/', $keyword);
            if (count($keywordEx) === 3) {
                $newKeyword = str_pad($keywordEx[2], 4, 20, STR_PAD_LEFT).'-'.str_pad($keywordEx[0], 2, 0, STR_PAD_LEFT).'-'.str_pad($keywordEx[1], 2, 0, STR_PAD_LEFT);
            } elseif (count($keywordEx) === 2) {
                if (strlen($keywordEx[1]) === 4) {
                    $newKeyword = str_pad($keywordEx[1], 4, 20, STR_PAD_LEFT).'-'.str_pad($keywordEx[0], 2, 0, STR_PAD_LEFT);
                } else {
                    $newKeyword = str_pad($keywordEx[0], 2, 0, STR_PAD_LEFT).'-'.str_pad($keywordEx[1], 2, 0, STR_PAD_LEFT);
                }
            } elseif (count($keywordEx) === 1) {
                if (strlen($keywordEx[0]) === 4) {
                    $newKeyword = str_pad($keywordEx[0], 4, 20, STR_PAD_LEFT);
                } else {
                    $newKeyword = str_pad($keywordEx[0], 2, 0, STR_PAD_LEFT);
                }
            } else {
                $newKeyword = 'bad_date';
            }
            $sql = "( select transaction_initiated_at from `transactions` where `transactions`.`deleted_at` is null and `transactions`.`contact_id` = `contacts`.`id` and `status` = 'complete' and (`transactions`.`tenant_id` = ? or `transactions`.`tenant_id` is null) ORDER BY `transactions`.`transaction_initiated_at` DESC LIMIT 1) LIKE '%{$newKeyword}%'";
            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        });
    }
    
    protected function addTagsColumn($dt)
    {
        $dt->addColumn('tags', function (Contact $contact) {
            return $contact->all_tags;
        });
    }

    protected function addCampaignsColumn($dt) {
        $dt->addColumn('campaigns', function (Contact $contact) {
            return $contact->transactionSplits()->completed()
            ->forCampaign()->has('campaign')->get()->map(function ($split) {
                return $split->campaign['name'];
            })->unique()->implode(', ');
        })
        ->filterColumn('campaigns', function ($query, $keyword) {
            $sql = "( select GROUP_CONCAT(campaigns.name) from `transaction_splits` inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` join `campaigns` on `campaigns`.`id` = `transaction_splits`.`campaign_id` where `campaigns`.`deleted_at` is null and `campaigns`.`id` != 1 and `transactions`.`contact_id` = `contacts`.`id` and `transactions`.`status` = 'complete'  and `transaction_splits`.`deleted_at` is null and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null)) LIKE '%{$keyword}%'";

            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        }) ;
    }
    
    protected function addPurposesColumn($dt) {
        $dt->addColumn('purposes', function (Contact $contact) {
            return $contact->transactionSplits()->completed()->get()->map(function ($split) {
                $prefix = $split->purpose['parentPurpose']
                ? $split->purpose->parentPurpose['name'] . "\\" : '' ;
                return $prefix.$split->purpose['name'];
            })->unique()->implode(', ');
        })
        ->filterColumn('purposes', function ($query, $keyword) {
            $sql = "( select GROUP_CONCAT(purposes.name) from `transaction_splits` inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` join `purposes` on `purposes`.`id` = `transaction_splits`.`purpose_id` where `purposes`.`deleted_at` is null and `transactions`.`contact_id` = `contacts`.`id` and `transactions`.`status` = 'complete'  and `transaction_splits`.`deleted_at` is null and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null)) LIKE '%{$keyword}%'";

            $query->whereRaw($sql,[auth()->user()->tenant_id]);
        });
    }

    protected function addPreferredNameColumn($dt)
    {
        $dt->addColumn('preferred_name', function (Contact $contact) {
            return array_get($contact, 'preferred_name', array_get($contact, 'full_name'));
        });
    }
    
    protected function addFamilyNameColumn($dt)
    {
        $dt->addColumn('family_name', function (Contact $contact) {
            return array_get($contact, 'family.name');
        });
    }
    
    protected function addFamilyEnvelopeNameColumn($dt)
    {
        $dt->addColumn('family_envelope_name', function (Contact $contact) {
            return array_get($contact, 'family.envelope_name');
        });
    }
    
    protected function addMergedNameColumn($dt)
    {
        $dt->addColumn('merged_name', function (Contact $contact) {
            if (!empty(array_get($contact, 'family.envelope_name'))) {
                return array_get($contact, 'family.envelope_name');
            } elseif (!empty(array_get($contact, 'family.name'))) {
                return array_get($contact, 'family.name');
            } elseif (!empty(array_get($contact, 'preferred_name'))) {
                return array_get($contact, 'preferred_name');
            } else {
                return array_get($contact, 'full_name');
            }
        });
    }

    protected function addCreatedAtColumn($dt)
    {
        $dt->addColumn('created_at', function (Contact $contact) {
            return array_get($contact, 'created_at') ? date('m/d/Y H:i:s', strtotime(displayLocalDateTime(array_get($contact, 'created_at')))) : '';
        });
    }
    
    protected function addUpdatedAtColumn($dt)
    {
        $dt->addColumn('updated_at', function (Contact $contact) {
            return array_get($contact, 'updated_at') ? date('m/d/Y H:i:s', strtotime(displayLocalDateTime(array_get($contact, 'updated_at')))) : '';
        });
    }
    
    protected function addTotalSumColumn($dt)
    {
        $dtTemp = $dt;
        $dtTemp->filterRecords();
        $contactIds = $dtTemp->getQueryBuilder()->whereNull('deleted_at')->where('tenant_id', auth()->user()->tenant_id)->pluck('id')->toArray();
        
        if (!empty($contactIds)) {
            $search = $dt->request->get('search');
            $total = $this->getTotalSumAmount($search, $contactIds);
            $lifetimeTotal = $this->getLifetimeTotalSumAmount($search, $contactIds);
            
            $dt->addColumn('total_sum', function () use ($total) {
                return $total;
            });

            $dt->addColumn('lifetime_total_sum', function () use ($lifetimeTotal) {
                return $lifetimeTotal;
            });
        }
    }
    
    protected function addNotesColumn($dt)
    {
        $dt->addColumn('notes', function (Contact $contact) {
            return $contact->all_notes;
        });
    }
    
    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        // allows the current request in the object to be used in the scopes
        $request = new \Illuminate\Http\Request($this->datatables->getRequest()->toArray());
        $this
        ->addScope(new Scopes\TransactionCompletedScope($request))
        ->addScope(new Scopes\CustomTransactionScope($request))
        ->addScope(new Scopes\CustomTransactionAmountSumScope($request))
        ->addScope(new Scopes\CustomContactTagsScope($request))
        ->addScope(new Scopes\CustomContactExcludeTagsScope($request))
        ->addScope(new Scopes\CustomEventRegistrationScope($request))
        ->addScope(new Scopes\CustomRecurringDonorsScope($request))
        ->addScope(new Scopes\CustomLatentDonorsScope($request))
        ->addScope(new Scopes\CustomGroupScope($request))
        ->addScope(new Scopes\CustomPrimaryContactsScope($request))
        ->addScope(new Scopes\CustomContactDateScope($request))
        ->addScope(new Scopes\CustomContactTypeScope($request))
        ->addScope(new Scopes\CustomCustomFieldScope($request))
        ->addScope(new Scopes\CustomContactSearchForBlankScope($request))
        ->addScope(new Scopes\CustomContactSearchForNoBlankScope($request));
        $query = Contact::query()->with(['tags', 'family', 'addresses', 'notes']);

        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        $route = request('state_id')
        ? route('search.contacts.state.show',request('state_id'))
        : route('search.contacts.state.index');
        $parameters = $this->defaultParameters();
        $parameters = array_merge($parameters, $this->configInitComplete());
        $parameters = array_merge($parameters, $this->configStateSave(
            route('search.contacts.state.store'),
            $route
        ));
        
        if (auth()->user()->can('transaction-view')) {
            $parameters['drawCallback'] = 'function () { loadTotals(this) }';
        }
        
        $additionalfilters = $this->getAdditionalFilters();
        /**/
        // $buttons =
        return $this->builder()
        ->columns(self::getColumns())
        // ->minifiedAjax('')
        ->ajax([
            'url' => route('search.contacts'),
            'data' => "function (data) {
                $additionalfilters
            }",
            ] )
            // ->addAction(['width' => '80px'])
            ->parameters($parameters);
    }

    public function updateTags()
    {
        $contactIds = array_pluck($this->getAjaxResponseData(), 'id');
        $tagIds = $this->request()->tag_ids;
        $action = $this->request()->tag_action;
        
        if ($contactIds && $tagIds) {
            foreach ($tagIds as $tagId) {
                if ($action === 'add') {
                    Tag::find($tagId)->contacts()->attach($contactIds);
                } elseif ($action === 'remove') {
                    Tag::find($tagId)->contacts()->detach($contactIds);
                }
            }
        }
        
        return response()->json(['success' => true]);
    }

    public function saveSearch() {
        abort(400, 'somehow bypassed redirection ContactDataTable::store');
        return response()->json(request()->all());
    }

    public function getContactIds() {
        return response()->json( $this->getContactIdArray() );
    }

    public function getContactIdArray() {
        return array_pluck($this->ajax()->getData()->data, 'id');
    }

    protected function printColumns()
    {
        $this->printColumns = $this->request()->col_vis;
        return parent::printColumns();
    }

    protected function getDataForExport()
    {
        $this->exportColumns = $this->request()->col_vis;
        return parent::getDataForExport();
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected static function getColumns()
    {
        $columns = [
            ['data' => 'company', 'name' => 'company', 'title' => 'Organization'], // 0 see visible
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'First Name'], 
            ['data' => 'last_name', 'name' => 'last_name', 'title' => 'Last Name'],
            ['data' => 'email_1', 'name' => 'email_1', 'title' => 'Email 1'],
            ['data' => 'cell_phone', 'name' => 'cell_phone', 'title' => 'Cell Phone'],
            ['data' => 'city', 'name' => 'city', 'title' => 'City', 'orderable' => false],
            ['data' => 'region', 'name' => 'region', 'title' => 'Region', 'orderable' => false],
            ['data' => 'postal_code', 'name' => 'postal_code', 'title' => 'Postal Code', 'orderable' => false],
            ['data' => 'campaigns', 'name' => 'campaigns', 'title' => 'Campaigns', 'orderable' => false],
            ['data' => 'purposes', 'name' => 'purposes', 'title' => 'Purposes', 'orderable' => false],
            ['data' => 'email_2', 'name' => 'email_2', 'title' => 'Email 2'],
            ['data' => 'home_phone', 'name' => 'home_phone', 'title' => 'Home Phone'],
            ['data' => 'mailing_address_1', 'name' => 'mailing_address_1', 'title' => 'Mailing Address 1', 'orderable' => false],
            ['data' => 'mailing_address_2', 'name' => 'mailing_address_2', 'title' => 'Mailing Address 2', 'orderable' => false],
            ['data' => 'p_o_box', 'name' => 'p_o_box', 'title' => 'P O Box', 'orderable' => false],
            ['data' => 'tags', 'name' => 'tags', 'title' => 'Tags', 'orderable' => false],
            ['data' => 'preferred_name', 'name' => 'preferred_name', 'title' => 'Preferred Name'],
            ['data' => 'family_name', 'name' => 'family_name', 'title' => 'Family Name'],
            ['data' => 'family_envelope_name', 'name' => 'family_envelope_name', 'title' => 'Family Envelope Name'],
            ['data' => 'merged_name', 'name' => 'merged_name', 'title' => 'Merged Name'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
            ['data' => 'notes', 'name' => 'notes', 'title' => 'Notes'],
            ['data' => 'background_info', 'name' => 'background_info', 'title' => 'Background Info'],
            ['data' => 'link', 'name' => 'link', 'title' => 'Link', 'orderable' => false], // 15 see visible
        ];
        
        // only add total amount column if can view transactions
        if (auth()->user()->can('transaction-view')) {
            array_splice($columns, 3, 0, [
                ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Total Amount', 'orderable' => false], // total amount
                ['data' => 'lifetime_total', 'name' => 'lifetime_total', 'title' => 'Lifetime Total', 'orderable' => false] // lifetime total
            ]);
            
            array_splice($columns, 18, 0, [
                ['data' => 'last_transaction_amount', 'name' => 'last_transaction_amount', 'title' => 'Last Transaction Amount', 'orderable' => false], // total amount
                ['data' => 'last_transaction_date', 'name' => 'last_transaction_date', 'title' => 'Last Transaction Date', 'orderable' => false] // lifetime total
            ]);
        }
        
        return $columns;
    }

    private static function getColumnNames()
    {
        return array_map(function($col){
            return $col['name'];
        },self::getColumns());
    }

    public function getTableColumns()
    {
        return self::getColumnNames();
    }
    
    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'contacts_' . time();
    }

    protected function defaultParameters() {
        $action_buttons = [
            'excel',
            'csv',
            // 'pdf',
            'print',
            'pictureDirectory',
            'add_remove_tag',
            'saveSearch',
            'updateSearch',
            'emailOrPrint',
            'sendSms'
        ];
        
        $columns = [
            ['visible' => true],   // organization
            ['visible' => true],    // first name
            ['visible' => true],
            ['visible' => true],
            ['visible' => true],    
            ['visible' => true],    // city
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => false],
            ['visible' => true],    // link
        ];
        
        $columnDefs = [0,1,2,3,4,24];
        
        // only add total amount column if can view transactions
        if (auth()->user()->can('transaction-view')) {
            array_splice($columns, 3, 0, [['visible' => true], ['visible' => false]]);
            array_splice($columns, 18, 0, [['visible' => false], ['visible' => false]]);
            $columnDefs = [0,1,2,3,5,6,7,28];
        }
        
        return [
            'columns' => $columns,
            'columnDefs' => [
                [
                    'visible' => true,
                    'targets' => $columnDefs,
                ],
                [
                    'visible' => false,
                    'targets' => '_all',
                ],
                // [
                //     // Jesse didn't like this https://app.asana.com/0/1194629996910624/1195390986059559/f
                //     // total amount filtering
                //     'targets' => [4],
                //     'className' => 'text-right'
                // ],
            ],
            'oLanguage' => ['sProcessing' => '<div class="app-loader d-block">
                        <div class="spinner">
                            <div class="rect1"></div>
                            <div class="rect2"></div>
                            <div class="rect3"></div>
                            <div class="rect4"></div>
                            <div class="rect5"></div>
                        </div>
                    </div>'],
            'dom' =>
                "<'row mb-2'<'col-sm-12'B>>" .
                "<'row mb-2'<'col-sm-12 table-responsive'tr>>" .
                "<'row'<'col-sm-10'<'pull-left'li>p>>",
            'responsive' => true,
            'order' => [[1, 'asc']],
            'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, "All"]],
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<span class="fa fa-columns" data-toggle="tooltip" title="Manage Columns"></span> Manage Columns',
                    'className' => 'bg-white border-0'
                    // 'collectionLayout' => 'fixed two-column' // not sure why this has display issues
                ],
                [
                    'extend' => 'collection',
                    'text' => 'Actions',
                    'buttons' => $action_buttons,
                    'className' => 'd-none'
                ],
                // 'print',
                // [ 'extend' => 'print', 'exportOptions' => [ 'columns' => $exportVisibility ] ],
                ['extend' => 'reset', 'className' => 'resetButton d-none'],
                // 'reload',
            ],
        ];
    }

    protected function configInitComplete()
    {
        return [
            'initComplete' => "function () {
                var datatable = this;
                $('.toggleFiltersBtn').on('click', function () {
                    $('thead input, #searchFilters').fadeToggle()
                    // use if there are responsive display problems
                    window.LaravelDataTables['dataTableBuilder'].columns.adjust()
                })
                $('.resetButton').on('click', function() {
                    $('#filtersForm select').val(null)
                    $('thead input').val('')
                    $('#filtersForm')[0].reset()
                    datatable.api().columns().every(function () {
                        this.search('', false, false, true);
                    })
                    window.LaravelDataTables['dataTableBuilder'].draw()
                })
                {$this->configColumnSearch()}
            }"
        ];
    }

    protected function configColumnSearch()
    {
        return "
            window.adv_contact_search_dt = this
//            this.api().columns().every(function (val) {
//            if (window.LaravelDataTables['dataTableBuilder'].column(val).header().innerHTML == 'Link') return false;
//            var column = this;
//            var input = document.createElement(\"input\");
//            let checkbox = document.createElement(\"INPUT\");
//            checkbox.setAttribute(\"type\", \"checkbox\");
//            checkbox.setAttribute(\"name\", \"checkbox\");
//            checkbox.value = this.dataSrc();
//            let state = window.LaravelDataTables['dataTableBuilder'].state.loaded()
//            if(state && !_.isEmpty(state.columns)){
//                let columns = state.columns.map(col => col.search.search)
//                $(input).val(columns[val])
//            }
//            $(input).appendTo($('#column_filtering'))
//            $(checkbox).appendTo($('#column_filtering'))
//            $(checkbox).change(function(){
//                if(this.checked){
//                $(input).attr('readonly', true).val('^$');
//                column.search('^$', true, false, true).draw()
//                }else{
//                $(input).attr('readonly', false).val('');
//                column.search('', false, false, true).draw()
//                }
//            })
//            $(input).addClass('form-control')
//            input.placeholder = window.LaravelDataTables['dataTableBuilder'].column(val).header().innerHTML
//            $(input).wrap(\"<div class='col-lg-3 col-sm-6 mb-2'></div>\")
//            .on('keyup', 
//               _.debounce(function(){column.search($(this).val(), false, false, true).draw();},400)
//            );
//        });
        ";
    }

    protected function getAdditionalFilters() {
        $additionalfilters = 'var cf_temp; ';
        foreach (self::$custom_filters as $filter) {
            $additionalfilters .= "data.search.$filter = (cf_temp = $('input[name=$filter]:checked').val()) 
            ? cf_temp : $('input,select').filter('[name=$filter]').val();";
        }
        return $additionalfilters;
    }

    protected function setAdditionalFilters() {
        $additionalfilters = 'var cf_temp, value; if (data.search) {  ';
        foreach (self::$custom_filters as $filter) {
            $additionalfilters .= "value = data.search.$filter; if (typeof value != 'array' && typeof value != 'object') { $(`input[type=radio][id$=\${value}]`).filter('[name=$filter]').attr('checked', true); }; $('input[type!=radio],select').filter('[name=$filter]').val(value);";
        }
        $additionalfilters .= "   }";
        return $additionalfilters;
    }


    protected function configStateSave($url,$loadurl)
    {
        $additionalfilters = $this->getAdditionalFilters();
        return [
            'stateSave' => true,
            'stateSaveCallback' => 'function (settings, data) {
                // Send an Ajax request to the server with the state object
                $.ajax( {
                  "url": "'.$url.'",
                  "data": data,
                  "dataType": "json",
                  "type": "POST",
                  "success": function (response) { 
                      // console.log(response) 
                  },
                } );
            }',
            "stateSaveParams" => 'function (settings, data) {
                data.class_saved_by = "'.addslashes(self::class).'";
                '.$additionalfilters.'
            }',
            'stateLoadCallback' => 'function (settings, callback) {
                // Get an Ajax request from the server with the state object
                $.ajax( {
                  "url": "'.$loadurl.'",
                  "dataType": "json",
                  "dataSrc": "",
                  "success": function (data) { 
                      // $("[type=search]").val(data.search.value)
                      // console.log("stateLoadCallback", data) 
                      '.$this->setAdditionalFilters().'
                      
                      if(_.isEmpty(data)){
                          console.log("auto-save state disabled, disabling error message")
                          // Swal.fire("Invalid Saved Search","No saved search found","error").then(() => {window.location = window.location.href.split("?")[0];})
                          callback([])
                          return;
                      }
                      // Work Around for Column Visibility Bug. Abnormal Active state when stateLoadCallback is on.
                      if (data.columns) {
                          data.columns.forEach((col) => {
                            col.visible = (col.visible === "true" || col.visible === true ? true : false)
                          })
                      }
                      callback(data)
                  },
                } );
            }',
        ];
    }

    public static function createFromState($state)
    {
        if (!is_array($state) && get_class($state) == \App\Models\DatatableState::class) {
            $state = $state->toArray();
        }
        
        if (array_get($state, 'search.search')) {
            array_set($state, 'search.value', array_get($state, 'search.search'));
        }
        
        if (isset($state['columns']) && is_array($state['columns'])) {
            for ($i = 0; $i < count($state['columns']); $i++) {
                if (array_get($state['columns'][$i], 'search.search')) {
                    array_set($state['columns'][$i], 'search.value', array_get($state['columns'][$i], 'search.search'));
                }
            }
        }
        
        $names = self::getColumnNames();
        
        if (isset($state['columns'])) {
            foreach ($state['columns'] as $key => $col) {
                if (!array_key_exists($key, $names)) {
                    continue;
                }
                
                $state['columns'][$key]['data'] = $names[$key];
            }
        }
        
        array_set($state, 'length', -1);
        
        // $r = new \Illuminate\Http\Request(['data'=>$state]);
        $r = new \Illuminate\Http\Request($state);
        $r = new \Yajra\Datatables\Request($r);
        $dt = new \Yajra\Datatables\Datatables($r);
        $cdt = new self($dt, app('view'));
        
        return $cdt;
    }

    public static function createState($options = []) {
        $values = self::stateDefaults($options);
        $state = new \App\Models\DatatableState($values);
        $state->save();
        $state->refresh();

        return $state;
    }

    /**
     * @param  array  $options  An array of values with attributes from the columns of DatatableState
     * @return [array]
     */
    public static function stateDefaults($options = [], $always_set_time = true)
    {
        $defaults = [
            'uri' => self::DEFAULT_URI,
            'is_user_search' => array_get($options,'is_user_search', 0),
            // 'is_user_search' => $request->get('is_user_search', 0),
            'name' => array_get($options,'name',null),
            // 'name' => request('name',null),
        ];

        if ($always_set_time) $defaults['time'] = time()*1000;
        if (array_has($options,'time')) $defaults['time'] = $options['time'];
        // if auto-saving, scope by current user
        if (!$defaults['is_user_search'] && auth()->check()) $options['created_by'] = auth()->user()->id;


        foreach (self::$custom_filters as $filter) {
            if (!array_has($options,'search') || !array_has($options['search'], $filter)) continue;
            $value = $options['search'][$filter];
            if (is_array($value)) {
                $value = array_map(function($v) { return is_numeric($v) ? (string)$v: $v; }, $value);
            }
            $defaults['search'][$filter] = $value;
        }

        return $defaults;
    }

    public static function getCustomFilters() { return self::$custom_filters; }
    
    private function getTotalSumAmount($search, $contactIds)
    {
        $start = array_has($search, 'transaction_date_min') ? localizeDate(array_get($search, 'transaction_date_min'), 'start') : null;
        $end = array_has($search, 'transaction_date_max') ? localizeDate(array_get($search, 'transaction_date_max'), 'end') : null;
        $min = array_has($search, 'transaction_amount_min') ? array_get($search, 'transaction_amount_min') : null;
        $max = array_has($search, 'transaction_amount_max') ? array_get($search, 'transaction_amount_max') : null;
        $useSum = array_get($search, 'transaction_amount_use_sum', 1);
        $purposes = array_has($search, 'transaction_purposes') ? array_get($search, 'transaction_purposes') : null;
        $campaigns = array_has($search, 'transaction_campaigns') ? array_get($search, 'transaction_campaigns') : null;
        $tags = array_has($search, 'transaction_tags') ? array_get($search, 'transaction_tags') : null;
        
        $total = TransactionSplit::where(function ($q) use ($contactIds, $search, $start, $end, $min, $max, $useSum, $purposes, $campaigns, $tags) {
            $q->completed();
            
            if (!$useSum) {
                if (array_has($search, 'transaction_amount_min') && $min) {
                    $q->where('amount','>=', $min);
                } 

                if(array_has($search, 'transaction_amount_max') && $max) {
                    $q->where('amount','<=', $max); 
                }
            }

            $q->whereHas('transaction', function ($query) use ($contactIds, $search, $start, $end, $purposes, $campaigns) {
                if ($contactIds) {
                    $query->whereIn('contact_id', $contactIds);
                }
                
                if (array_has($search, 'transaction_date_min') && $start) {
                    $query->where('transaction_initiated_at','>=', $start);
                }

                if (array_has($search, 'transaction_date_max') && $end) {
                    $query->where('transaction_initiated_at','<=', $end);
                }
                
                if (array_has($search, 'transaction_purposes') && $purposes) {
                    $query->whereIn('purpose_id', $purposes);
                }

                if (array_has($search, 'transaction_campaigns') && $campaigns) {
                    $query->whereIn('campaign_id', $campaigns);
                }
            });
            
            if (array_has($search, 'transaction_tags') && $tags) {
                $q->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('id', $tags);
                });
            }
        })->sum('amount');
        
        if (empty($total)) {
            $total = 0;
        }
        
        $totalFormatted = '$'.number_format($total, 2);
        
        return $totalFormatted;
    }
    
    private function getLifetimeTotalSumAmount($search, $contactIds)
    {
        $min = array_has($search, 'transaction_amount_min') ? array_get($search, 'transaction_amount_min') : null;
        $max = array_has($search, 'transaction_amount_max') ? array_get($search, 'transaction_amount_max') : null;
        $useSum = array_get($search, 'transaction_amount_use_sum', 1);
        $purposes = array_has($search, 'transaction_purposes') ? array_get($search, 'transaction_purposes') : null;
        $campaigns = array_has($search, 'transaction_campaigns') ? array_get($search, 'transaction_campaigns') : null;
        $tags = array_has($search, 'transaction_tags') ? array_get($search, 'transaction_tags') : null;
        
        $total = TransactionSplit::where(function ($q) use ($contactIds, $search, $min, $max, $useSum, $purposes, $campaigns, $tags) {
            $q->completed();
            
            if (!$useSum) {
                if (array_has($search, 'transaction_amount_min') && $min) {
                    $q->where('amount','>=', $min);
                } 

                if(array_has($search, 'transaction_amount_max') && $max) {
                    $q->where('amount','<=', $max); 
                }
            }
            
            $q->whereHas('transaction', function ($query) use ($contactIds, $search, $purposes, $campaigns) {
                if ($contactIds) {
                    $query->whereIn('contact_id', $contactIds);
                }
                
                if (array_has($search, 'transaction_purposes') && $purposes) {
                    $query->whereIn('purpose_id', $purposes);
                }

                if (array_has($search, 'transaction_campaigns') && $campaigns) {
                    $query->whereIn('campaign_id', $campaigns);
                }
            });
            
            if (array_has($search, 'transaction_tags') && $tags) {
                $q->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('id', $tags);
                });
            }
        })->sum('amount');
        
        if (empty($total)) {
            $total = 0;
        }
        
        $totalFormatted = '$'.number_format($total, 2);
        
        return $totalFormatted;
    }
}
