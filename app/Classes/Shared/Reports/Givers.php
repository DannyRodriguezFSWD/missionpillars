<?php

namespace App\Classes\Shared\Reports;

use App\Constants;
use App\Models\Contact;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\TransactionSplit;
use Illuminate\Support\Facades\DB;
use App\Classes\Shared\Contacts\FilterContacts;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use App\Models\Purpose;
/**
 * Description of Chart
 *
 * @author josemiguel
 */
class Givers {

    public function newGivers($request){
        $from = !is_null(array_get($request, 'from')) ? localizeDate(array_get($request, 'from'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to = !is_null(array_get($request, 'to')) ? localizeDate(array_get($request, 'to'), 'end') : localizeDate(date('Y-m-d'), 'end');
        
        $list = array_get($request, 'list', 0);
        $in_tags = array_get($request, 'in_tags');
        $out_tags = array_get($request, 'out_tags');
        $use_search_in_contacts = false;
        
        $type = array_get($request, 'transaction_type');

        if((int)$list > 0 || !is_null($in_tags) || !is_null($out_tags)){
            $use_search_in_contacts = true;
        }

        $search_in_contacts = null;
        if((int)$list > 0){
            $search_in_contacts = FilterContacts::byListTagsSystem($list, $in_tags, $out_tags);
        }
        else{
            $search_in_contacts = FilterContacts::byTags($in_tags, $out_tags);
        }
        
        $builder = Contact::whereHas('transactions', function($q) use($from, $to, $type){
            $q->whereHas('splits', function($q) use ($type) {
                if ($type && $type !== 'all') {
                    $q->where([
                        ['type', '=', 'donation'],
                    ]);
                }
            })->where([
                ['status', '=', 'complete'],
                ['transaction_initiated_at', '<', $from],
            ]);
        });
        
        if($use_search_in_contacts){
            $builder->whereIn('id', array_pluck($search_in_contacts, 'id'));
        }
        
        $old_givers = $builder->get();
        
        $builder = Contact::select([
            'contacts.*',
            DB::raw('SUM(transaction_splits.amount) as total_amount'),
            DB::raw('COUNT(transactions.id) as total_transactions'),
        ])->join('transactions', 'transactions.contact_id', '=', 'contacts.id')
        ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
        ->where([
            ['transactions.status', '=', 'complete'],
            ['transactions.deleted_at', '=', null],
        ])->whereBetween('transactions.transaction_initiated_at', [$from, $to]);
        
        if ($type && $type !== 'all') {
            $builder->where([
                ['transaction_splits.type', '=', $type],
            ]);
        }
        
        if($use_search_in_contacts){
            $builder->whereIn('contacts.id', array_pluck($search_in_contacts, 'id'));
        }

        $builder->whereNotIn('contacts.id', array_pluck($old_givers, 'id'))->groupBy('contacts.id');
        $new_givers = $builder->get();
        
        return $new_givers;
    }

    public function latentGivers($request){
        $from1 = !is_null(array_get($request, 'from')) ? localizeDate(array_get($request, 'from'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to1 = !is_null(array_get($request, 'to')) ? localizeDate(array_get($request, 'to'), 'end') : localizeDate(date('Y-m-d'), 'end');        
        
        $from2 = !is_null(array_get($request, 'from2')) ? localizeDate(array_get($request, 'from2'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to2 = !is_null(array_get($request, 'to2')) ? localizeDate(array_get($request, 'to2'), 'end') : localizeDate(date('Y-m-d'), 'end'); 

        $list = array_get($request, 'list', 0);
        $in_tags = array_get($request, 'in_tags');
        $out_tags = array_get($request, 'out_tags');
        $use_search_in_contacts = false;

        if((int)$list > 0 || !is_null($in_tags) || !is_null($out_tags)){
            $use_search_in_contacts = true;
        }

        $search_in_contacts = null;
        if($list > 0 && !is_null($in_tags) && !is_null($out_tags)){
            $search_in_contacts = FilterContacts::byListTagsSystem($list, $in_tags, $out_tags);
            //dd(1, $search_in_contacts);
        }
        else{
            $search_in_contacts = FilterContacts::byTags($in_tags, $out_tags);
            //dd(2, $search_in_contacts);
        }
        //dd(array_pluck($search_in_contacts, 'last_name'), $in_tags, $out_tags);
        $builder_in_range_1 = Contact::whereDoesntHave('transactions', function($q) use($from1, $to1){
            $q->whereBetween('transaction_initiated_at', [$from1, $to1]);
            $q->where('status', 'complete');
        });

        if($use_search_in_contacts){
            $builder_in_range_1->whereIn('id', array_pluck($search_in_contacts, 'id'));
        }
        $contacts_in_range_1 = $builder_in_range_1->get();//contact without transdactions in range 1
        
        $builder_in_range_2 = Contact::select([
            'contacts.*',
            DB::raw('SUM(transaction_splits.amount) as total_amount'),
            DB::raw('COUNT(transactions.id) as total_transactions'),
        ])->join('transactions', 'transactions.contact_id', '=', 'contacts.id')
        ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
        ->where([
            ['transactions.status', '=', 'complete'],
            ['transactions.deleted_at', '=', null],
        ])->whereBetween('transactions.transaction_initiated_at', [$from2, $to2])
        ->whereIn('contacts.id', array_pluck($contacts_in_range_1, 'id'));

        if($use_search_in_contacts){
            $builder_in_range_2->whereIn('contacts.id', array_pluck($search_in_contacts, 'id'));
        }
        $builder_in_range_2->groupBy('contacts.id');
        $contacts_in_range_2 = $builder_in_range_2->get();

        $data = [
            'contacts_in_range_1' => $contacts_in_range_1,
            'contacts_in_range_2' => $contacts_in_range_2
        ];
        
        return $data;
    }

    public function giversStatistics($request){
        $from = !is_null(array_get($request, 'from')) ? localizeDate(array_get($request, 'from'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to = !is_null(array_get($request, 'to')) ? localizeDate(array_get($request, 'to'), 'end') : localizeDate(date('Y-m-d'), 'end');
        
        $list = array_get($request, 'list', 0);
        $in_tags = array_get($request, 'in_tags');
        $out_tags = array_get($request, 'out_tags');
        $use_search_in_contacts = false;

        if((int)$list > 0 || !is_null($in_tags) || !is_null($out_tags)){
            $use_search_in_contacts = true;
        }

        $search_in_contacts = null;
        if($list > 0 && !is_null($in_tags) && !is_null($out_tags)){
            $search_in_contacts = FilterContacts::byListTagsSystem($list, $in_tags, $out_tags);
        }
        else{
            $search_in_contacts = FilterContacts::byTags($in_tags, $out_tags);
        }

        $builder = Contact::select([
            'contacts.*',
            DB::raw('SUM(transaction_splits.amount) as total_amount'),
            DB::raw('COUNT(transactions.id) as total_transactions'),
        ])->join('transactions', 'transactions.contact_id', '=', 'contacts.id')
        ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
        ->where([
            ['transactions.status', '=', 'complete'],
            ['transaction_splits.type', '=', 'donation'],
            ['transactions.deleted_at', '=', null]
        ])->whereBetween('transactions.transaction_initiated_at', [$from, $to]);

        if($use_search_in_contacts){
            $builder->whereIn('contacts.id', array_pluck($search_in_contacts, 'id'));
        }
        $builder->groupBy('contacts.id');
        $givers = $builder->get();
        return $givers;
    }

    public function purposesStatistics($request){
        $from = !is_null(array_get($request, 'from')) ? localizeDate(array_get($request, 'from'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to = !is_null(array_get($request, 'to')) ? localizeDate(array_get($request, 'to'), 'end') : localizeDate(date('Y-m-d'), 'end');
        
        $list = array_get($request, 'list', 0);
        $in_tags = array_get($request, 'in_tags');
        $out_tags = array_get($request, 'out_tags');
        $use_search_in_contacts = false;

        if((int)$list > 0 || !is_null($in_tags) || !is_null($out_tags)){
            $use_search_in_contacts = true;
        }

        $amount_ranges = array_get($request, 'amount_ranges', '[]');
        $ranges = json_decode($amount_ranges, true);

        $search_in_contacts = null;
        if($list > 0 && !is_null($in_tags) && !is_null($out_tags)){
            $search_in_contacts = FilterContacts::byListTagsSystem($list, $in_tags, $out_tags);
        }
        else {
            $search_in_contacts = FilterContacts::byTags($in_tags, $out_tags);
        }

        $transactionType = array_get($request, 'transaction_type', 'all');
        $transactionChannel = array_get($request, 'transaction_channel', null) ? explode(',', array_get($request, 'transaction_channel')) : [];
        $transactionOnlineOffline = array_get($request, 'transaction_online_offline', 'all');
        
        $purposes = [];
        
        $result = $this->getPurposesQueryGroupedByPurposeId($from, $to, $use_search_in_contacts, $search_in_contacts, null, null, $transactionType, $transactionChannel, $transactionOnlineOffline)->get();
        
        foreach ($ranges as $range) {
            foreach ($result as $record) {
                $record_result = $this->getPurposesQueryGroupedByPurposeId($from, $to, $use_search_in_contacts, $search_in_contacts, array_get($record, 'purpose.id'), $range, $transactionType, $transactionChannel, $transactionOnlineOffline)->get();
                
                // reject records that have no donations in range
                $totals = $record_result->toArray();
                
                if(in_array(array_get($record, 'purpose.id'), array_keys($purposes))){
                    $purpose = $purposes[array_get($record, 'purpose.id')];
                }
                else{
                    $purpose = [
                        'id' => array_get($record, 'purpose.id'),
                        'name' => array_get($record, 'purpose.name'),
                    ];
                }
                $rs = array_get($purpose, 'ranges', []);
                
                $total = reset($totals);
                do {
                    
                    $r = [
                        [
                            'value' => "#",
                            'total_transactions' => array_get($total, 'total_transactions', 0),
                        ],
                        [
                            'value' => sprintf("$%s", implode(' - ', [array_get($range, 'min', 0), array_get($range, 'max', 0)])),
                            'total_amount' => array_get($total, 'total_amount', 0),
                        ]
                    ];
                    array_push($rs, $r);
                    array_set($purpose, 'ranges', $rs);
                    $purposes[array_get($record, 'purpose.id')] = $purpose;
                    
                } while($total = next($totals));
                if (count($totals) == 0) continue;
            }
        }
        
        $col_total_transactions_sum = 0;
        $col_total_amount_sum = 0;
        foreach($purposes as $key => $purpose){
            $rs = array_get($purpose, 'ranges', []);
            $row_total_transactions_sum = 0;
            $row_total_amount_sum = 0;
            
            foreach ($rs as $range) {
                $row_total_transactions_sum += array_get($range, '0.total_transactions', 0);
                $row_total_amount_sum += array_get($range, '1.total_amount', 0);
            }
            $col_total_transactions_sum += $row_total_transactions_sum;
            $col_total_amount_sum += $row_total_amount_sum;

            $r = [
                [
                    'value' => "# Total",
                    'total_transactions' => $row_total_transactions_sum
                ],
                [
                    'value' => "$ Total",
                    'total_amount' => $row_total_amount_sum,
                ]
            ];
            
            array_push($rs, $r);
            array_set($purpose, 'ranges', $rs);
            $purposes[$key] = $purpose;
        }
        
        return [
            'purposes' => $purposes,
            'sum' => [
                'id' => 0,
                'name' => '',
                'ranges' => [
                    [], [], [], [], [
                        [
                            'value' => "# Total",
                            'total_transactions' => $col_total_transactions_sum
                        ],
                        [
                            'value' => "$ Total",
                            'total_amount' => $col_total_amount_sum,
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function getPurposesQueryGroupedByPurposeId($from, $to, $use_search_in_contacts, $search_in_contacts, $purpose_id = null, $range = null, $transactionType = 'all', $transactionChannel = [], $transactionOnlineOffline = 'all') {
        $builder = TransactionSplit::select([
            // 'transaction_splits.*',
            'transaction_splits.purpose_id',
            // DB::raw('COUNT(DISTINCT transactions.contact_id) as total_contacts'),
            DB::raw('SUM(transaction_splits.amount) as total_amount'),
            DB::raw('COUNT(DISTINCT transaction_splits.transaction_id) as total_transactions'), // ASSUMPTION in the event that a transaction is split  all splits with the same purpose count as 1
        ])
        ->whereHas('transaction', function($query) use ($from, $to, $use_search_in_contacts, $search_in_contacts, $range, $transactionChannel, $transactionOnlineOffline) {
            if ($range) $query->whereBetween('amount',[$range['min'],$range['max']]);
            $query->completed();
            $query->whereBetween('transaction_initiated_at', [$from, $to] );
            if($use_search_in_contacts){
                $query->whereIn('contact_id', array_pluck($search_in_contacts, 'id'));
            }
            if (count($transactionChannel) > 0) {
                $query->whereIn('channel', $transactionChannel);
            }
            if ($transactionOnlineOffline === 'online') {
                $query->where('system_created_by', 'Continue to Give');
            } elseif ($transactionOnlineOffline === 'offline') {
                $query->where(function ($onlineOfflineQuery) {
                    $onlineOfflineQuery->where('system_created_by', '<>', 'Continue to Give')->orWhereNull('system_created_by');
                });
            }
            $query->whereNull('parent_transaction_id');
        })
        ->whereNotNull('transaction_splits.purpose_id');
        
        if ($transactionType !== 'all') {
            $builder->where('type', $transactionType);
        }
        
        $builder->with('purpose:id,name');
        if ($purpose_id) $builder->where('transaction_splits.purpose_id', '=', $purpose_id);


        $builder->groupBy('transaction_splits.purpose_id');
        return $builder;
    }

    public function campaignsStatistics($request){
        $from = !is_null(array_get($request, 'from')) ? localizeDate(array_get($request, 'from'), 'start') : localizeDate(date('Y-m-d'), 'start');
        $to = !is_null(array_get($request, 'to')) ? localizeDate(array_get($request, 'to'), 'end') : localizeDate(date('Y-m-d'), 'end');
        
        $list = array_get($request, 'list', 0);
        $in_tags = array_get($request, 'in_tags');
        $out_tags = array_get($request, 'out_tags');
        $use_search_in_contacts = false;

        if((int)$list > 0 || !is_null($in_tags) || !is_null($out_tags)){
            $use_search_in_contacts = true;
        }

        $amount_ranges = array_get($request, 'amount_ranges', '[]');
        $ranges = json_decode($amount_ranges, true);

        $search_in_contacts = null;
        if($list > 0 && !is_null($in_tags) && !is_null($out_tags)){
            $search_in_contacts = FilterContacts::byListTagsSystem($list, $in_tags, $out_tags);
        }
        else {
            $search_in_contacts = FilterContacts::byTags($in_tags, $out_tags);
        }

        $transactionType = array_get($request, 'transaction_type', 'all');
        $transactionChannel = array_get($request, 'transaction_channel', null) ? explode(',', array_get($request, 'transaction_channel')) : [];
        $transactionOnlineOffline = array_get($request, 'transaction_online_offline', 'all');
        
        $campaigns = [];
        
        $result = $this->getCampaignsQueryGroupedByCampaignId($from, $to, $use_search_in_contacts, $search_in_contacts, null, null, $transactionType, $transactionChannel, $transactionOnlineOffline)->get();
        
        foreach ($ranges as $range) {
            foreach ($result as $record) {
                $record_result = $this->getCampaignsQueryGroupedByCampaignId($from, $to, $use_search_in_contacts, $search_in_contacts, array_get($record, 'campaign.id'), $range, $transactionType, $transactionChannel, $transactionOnlineOffline)->get();
                
                // reject records that have no donations in range
                $totals = $record_result->toArray();
                
                if(in_array(array_get($record, 'campaign.id'), array_keys($campaigns))){
                    $campaign = $campaigns[array_get($record, 'campaign.id')];
                }
                else{
                    $campaign = [
                        'id' => array_get($record, 'campaign.id'),
                        'name' => array_get($record, 'campaign.name'),
                    ];
                }
                $rs = array_get($campaign, 'ranges', []);
                
                $total = reset($totals);
                do {
                    
                    $r = [
                        [
                            'value' => "#",
                            'total_transactions' => array_get($total, 'total_transactions', 0),
                        ],
                        [
                            'value' => sprintf("$%s", implode(' - ', [array_get($range, 'min', 0), array_get($range, 'max', 0)])),
                            'total_amount' => array_get($total, 'total_amount', 0),
                        ]
                    ];
                    array_push($rs, $r);
                    array_set($campaign, 'ranges', $rs);
                    $campaigns[array_get($record, 'campaign.id')] = $campaign;
                    
                } while($total = next($totals));
                if (count($totals) == 0) continue;
            }
        }
        
        $col_total_transactions_sum = 0;
        $col_total_amount_sum = 0;
        foreach($campaigns as $key => $campaign){
            $rs = array_get($campaign, 'ranges', []);
            $row_total_transactions_sum = 0;
            $row_total_amount_sum = 0;
            
            foreach ($rs as $range) {
                $row_total_transactions_sum += array_get($range, '0.total_transactions', 0);
                $row_total_amount_sum += array_get($range, '1.total_amount', 0);
            }
            $col_total_transactions_sum += $row_total_transactions_sum;
            $col_total_amount_sum += $row_total_amount_sum;

            $r = [
                [
                    'value' => "# Total",
                    'total_transactions' => $row_total_transactions_sum
                ],
                [
                    'value' => "$ Total",
                    'total_amount' => $row_total_amount_sum,
                ]
            ];
            
            array_push($rs, $r);
            array_set($campaign, 'ranges', $rs);
            $campaigns[$key] = $campaign;
        }
        
        return [
            'campaigns' => $campaigns,
            'sum' => [
                'id' => 0,
                'name' => '',
                'ranges' => [
                    [], [], [], [], [
                        [
                            'value' => "# Total",
                            'total_transactions' => $col_total_transactions_sum
                        ],
                        [
                            'value' => "$ Total",
                            'total_amount' => $col_total_amount_sum,
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function getCampaignsQueryGroupedByCampaignId($from, $to, $use_search_in_contacts, $search_in_contacts, $campaign_id = null, $range = null, $transactionType = 'all', $transactionChannel = [], $transactionOnlineOffline = 'all') {
        $builder = TransactionSplit::select([
            // 'transaction_splits.*',
            'transaction_splits.campaign_id',
            // DB::raw('COUNT(DISTINCT transactions.contact_id) as total_contacts'),
            DB::raw('SUM(transaction_splits.amount) as total_amount'),
            DB::raw('COUNT(DISTINCT transaction_splits.transaction_id) as total_transactions'), // ASSUMPTION in the event that a transaction is split  all splits with the same campaign count as 1
        ])
        ->whereHas('transaction', function($query) use ($from, $to, $use_search_in_contacts, $search_in_contacts, $range, $transactionChannel, $transactionOnlineOffline) {
            if ($range) $query->whereBetween('amount',[$range['min'],$range['max']]);
            $query->completed();
            $query->whereBetween('transaction_initiated_at', [$from, $to] );
            if($use_search_in_contacts){
                $query->whereIn('contact_id', array_pluck($search_in_contacts, 'id'));
            }
            if (count($transactionChannel) > 0) {
                $query->whereIn('channel', $transactionChannel);
            }
            if ($transactionOnlineOffline === 'online') {
                $query->where('system_created_by', 'Continue to Give');
            } elseif ($transactionOnlineOffline === 'offline') {
                $query->where(function ($onlineOfflineQuery) {
                    $onlineOfflineQuery->where('system_created_by', '<>', 'Continue to Give')->orWhereNull('system_created_by');
                });
            }
            $query->whereNull('parent_transaction_id');
        })
        ->whereNotNull('transaction_splits.campaign_id');
        
        if ($transactionType !== 'all') {
            $builder->where('type', $transactionType);
        }
        
        $builder->with('campaign:id,name');
        if ($campaign_id) $builder->where('transaction_splits.campaign_id', '=', $campaign_id);


        $builder->groupBy('transaction_splits.campaign_id');
        return $builder;
    }
}
