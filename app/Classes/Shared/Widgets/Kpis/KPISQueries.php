<?php

namespace App\Classes\Shared\Widgets\Kpis;

use App\Models\Transaction;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Description of KPISQueries
 *
 * @author josemiguel
 */
class KPISQueries {

    public static function query($withPledges = false) {
        if ($withPledges) {
            
        } else {
            $transactions = Transaction::whereHas('template', function($query) {
                        $query->where([
                            ['is_pledge', '=', false],
                        ]);
                    })
                    ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
                    ->where([
                ['transactions.status', '=', 'complete'],
                ['transactions.tenant_id', '=', auth()->user()->tenant->id],
                ['transactions.deleted_at', '=', null],
            ]);
        }

        return $transactions;
    }

    public static function averageGiftRunQuery($from, $to) {
        $query = self::query()->join('contacts', 'transactions.contact_id', '=', 'contacts.id');
        $query->select(DB::raw("FORMAT((SUM(transaction_splits.amount) / IF(COUNT(contacts.id) <= 0, 1, COUNT(contacts.id))), 2) as value, YEAR(transactions.transaction_last_updated_at) as year"));
        $query->whereBetween('transactions.transaction_last_updated_at', [$from, $to]);
        $query->groupBy(DB::raw('year'));
        $query->orderBy(DB::raw('year'), 'desc');
        $transactions = $query->get();

        if (count($transactions) > 0) {
            $current = array_first($transactions, function ($value, $key) {
                return !is_null($value);
            }, []);
            $last = $transactions[count($transactions) - 1];

            if (array_get($current, 'value') > array_get($last, 'value')) {
                $status = 'up';
            } else if (array_get($current, 'value') < array_get($last, 'value')) {
                $status = 'down';
            } else {
                $status = 'equals';
            }

            $kpi = [
                'type' => 'money',
                'title' => __('Average Gift'),
                'description' => __('Gifts amount received divided by the number of gifts received for that year'),
                'status' => $status,
                'current' => $current->toArray(),
                'last' => $last->toArray()
            ];

            return $kpi;
        }

        return [];
    }

    public static function averageAnnualGivingDonorRunQuery($from, $to) {
        $query = self::query();
        $query->select(DB::raw("FORMAT((SUM(transaction_splits.amount) / IF(COUNT(DISTINCT(transactions.contact_id)) <= 0, 1, COUNT(DISTINCT(transactions.contact_id)))), 2) as value, YEAR(transactions.transaction_last_updated_at) as year"));
        $query->whereBetween('transactions.transaction_last_updated_at', [$from, $to]);
        $query->groupBy(DB::raw('year'));
        $query->orderBy(DB::raw('year'), 'desc');
        $transactions = $query->get();

        if (count($transactions) > 0) {
            $current = array_first($transactions, function ($value, $key) {
                return !is_null($value);
            }, []);
            $last = $transactions[count($transactions) - 1];

            if (array_get($current, 'value') > array_get($last, 'value')) {
                $status = 'up';
            } else if (array_get($current, 'value') < array_get($last, 'value')) {
                $status = 'down';
            } else {
                $status = 'equals';
            }

            $kpi = [
                'type' => 'money',
                'title' => __('Average Annual Giving/Donor'),
                'description' => __('Gifts amount received divided by the number of donors for that year'),
                'status' => $status,
                'current' => $current->toArray(),
                'last' => $last->toArray()
            ];

            return $kpi;
        }

        return [];
    }

    public static function donorsInDatabaseRunQuery($from, $to) {
        $donors = Tag::orderBy('id', 'asc')->first();
        $currentDB = $donors->contacts()->select(DB::raw('COUNT(id) as value, YEAR(created_at) as year'))->first();
        $lastDB = $donors->contacts()->select(DB::raw('COUNT(id) as value, YEAR(created_at) as year'))
                        ->whereBetween('created_at', [$from, $to])->first();

        if (array_get($currentDB, 'value') > array_get($lastDB, 'value')) {
            $status = 'up';
        } else if (array_get($currentDB, 'value') < array_get($lastDB, 'value')) {
            $status = 'down';
        } else {
            $status = 'equals';
        }

        $current = $currentDB->toArray();
        $last = $lastDB->toArray();

        $new = array_get($currentDB, 'value', 0) - array_get($lastDB, 'value');
        $old = array_get($lastDB, 'value') ?: 1;
        $percent = number_format($new * 100 / $old, 2);

        array_set($current, 'percent', $percent);
        array_set($current, 'number', $new);
        array_set($last, 'number', $old);

        $kpi = [
            'type' => 'number_percent',
            'title' => __('Donors in Database'),
            'description' => __('Total number of donors in database'),
            'status' => $status,
            'current' => $current,
            'last' => $last
        ];

        return $kpi;
    }

    public static function donorsRetentionRateRunQuery($from, $to) {
        $donors = Tag::orderBy('id', 'asc')->first();

        $beforeLastFullYear = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [
                                $from->copy()->subYears(2)->startOfYear(),
                                $to->copy()->subYears(2)->endOfYear()
                            ]);
                        })->get();

        $ids = array_pluck($beforeLastFullYear, 'id');
        $lastYearRetention = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [$from, $to]);
                        })
                        ->whereIn('id', $ids)->get();

        $lastYear = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [
                                $from->copy()->subYear()->startOfYear(),
                                $to->copy()->subYear()->endOfYear()
                            ]);
                        })->get();

        $ids = array_pluck($lastYear, 'id');
        $currentYearRetention = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [$from, $to]);
                        })
                        ->whereIn('id', $ids)->get();

        $contacts = $donors->contacts()
                        ->whereBetween('created_at', [
                            $from->copy()->subYear()->startOfYear(),
                            $to->copy()->subYear()->endOfYear()
                        ])->get();

        $div = $contacts->count() > 0 ?: 1;
        $lastYearRetentionPercent = $lastYearRetention->count() * 100 / $div;
        $currentYearRetentionPercent = $currentYearRetention->count() * 100 / $div;

        if ($currentYearRetention > $lastYearRetention) {
            $status = 'up';
        } else if ($currentYearRetention < $lastYearRetention) {
            $status = 'down';
        } else {
            $status = 'equals';
        }

        $current = [
            'value' => $currentYearRetention->count(),
            'year' => $from->year,
            'percent' => number_format($currentYearRetentionPercent, 2),
            'number' => $currentYearRetention->count()
        ];
        $last = [
            'value' => $lastYearRetention->count(),
            'year' => $from->copy()->subYear()->year,
            'percent' => number_format($lastYearRetentionPercent, 2),
            'number' => $lastYearRetention->count()
        ];

        $kpi = [
            'type' => 'number_percent',
            'title' => __('Donor Retention Rate'),
            'description' => __('The number of donors who gave last year and gave again this year, divided by the total number of donors last year.'),
            'status' => $status,
            'current' => $current,
            'last' => $last
        ];
        return $kpi;
    }

    public static function donorAttritionRateRunQuery($from, $to) {
        $donors = Tag::orderBy('id', 'asc')->first();
        $currentYear = $donors->contacts()
                ->whereHas('transactions', function($query) use ($from, $to) {
                    $query->whereBetween('transaction_last_updated_at', [$from, $to]);
                })->get();

        $lastYear = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [
                                $from->copy()->subYear()->startOfYear(),
                                $to->copy()->subYear()->endOfYear()
                            ]);
                        })->get();
                        
        $ids = array_pluck($lastYear, 'id');
        $lastYearAttrition = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [
                                $from->copy()->subYears(2)->startOfYear(),
                                $to->copy()->subYears(2)->endOfYear()
                            ]);
                        })
                        ->whereNotIn('id', $ids)->get();

        $ids = array_pluck($currentYear, 'id');
        $currentYearAttrition = $donors->contacts()
                        ->whereHas('transactions', function($query) use ($from, $to) {
                            $query->whereBetween('transaction_last_updated_at', [
                                $from->copy()->subYear()->startOfYear(),
                                $to->copy()->subYear()->endOfYear()
                            ]);
                        })
                        ->whereNotIn('id', $ids)->get();

        $contacts = $donors->contacts()
                        ->whereBetween('created_at', [
                            $from->copy()->subYear()->startOfYear(),
                            $to->copy()->subYear()->endOfYear()
                        ])->get();

        $div = $contacts->count() > 0 ?: 1;
        $lastYearAttritionPercent = $lastYearAttrition->count() * 100 / $div;
        $currentYearAttritionPercent = $currentYearAttrition->count() * 100 / $div;

        if ($currentYearAttritionPercent > $lastYearAttritionPercent) {
            $status = 'up';
        } else if ($currentYearAttritionPercent < $lastYearAttritionPercent) {
            $status = 'down';
        } else {
            $status = 'equals';
        }

        $current = [
            'value' => $currentYearAttrition->count(),
            'year' => $from->year,
            'percent' => number_format($currentYearAttritionPercent, 2),
            'number' => $currentYearAttrition->count()
        ];
        $last = [
            'value' => $lastYearAttrition->count(),
            'year' => $from->copy()->subYear()->year,
            'percent' => number_format($lastYearAttritionPercent, 2),
            'number' => $lastYearAttrition->count()
        ];

        $kpi = [
            'type' => 'inverted_number_percent',
            'title' => __('Donor Attrition Rate'),
            'description' => __('The number of donors who gave last year but not this year, divided by the total number of donors last year.'),
            'status' => $status,
            'current' => $current,
            'last' => $last
        ];
        return $kpi;
    }
    
    public static function donorParticipationRateRunQuery($from, $to) {
        $donors = Tag::orderBy('id', 'asc')->first();
        $lastYear = $donors->contacts()->whereBetween('created_at', [
            $from->copy()->subYear()->startOfYear(),
            $to->copy()->subYear()->endOfYear()
        ])->get();
        
        //Carbon::createFromTimestamp(-1) creates a far away datetime in the past (1969-12-31 18:59:59)
        $lastYearDatabase = $donors->contacts()->whereBetween('created_at', [
            Carbon::createFromTimestamp(-1),
            $to->copy()->subYear()->endOfYear()
        ])->get();
        
        $currentYear = $donors->contacts()->whereBetween('created_at', [$from, $to])->get();
        $currentDatabase = $donors->contacts;
        
        $div = $lastYearDatabase->count() > 0 ?: 1;
        $lastDonorParticipationPercent = $lastYear->count() * 100 / $div;
        $currentDonorParticipationPercent = $currentYear->count() * 100 / $div;

        if ($currentDonorParticipationPercent > 0) {
            $status = 'up';
        } else if ($currentDonorParticipationPercent <= 0) {
            $status = 'down';
        } else {
            $status = 'equals';
        }

        $current = [
            'value' => $currentYear->count(),
            'year' => $from->year,
            'percent' => number_format($currentDonorParticipationPercent, 2),
            'number' => $currentYear->count()
        ];
        
        $last = [
            'value' => $lastYear->count(),
            'year' => $from->copy()->subYear()->year,
            'percent' => number_format($lastDonorParticipationPercent, 2),
            'number' => $lastYear->count()
        ];
        
        $kpi = [
            'type' => 'number_percent',
            'title' => __('Donor Participation Rate'),
            'description' => __('The total number of donors in a year divided by the number of donors in the database at the end of that year.'),
            'status' => $status,
            'current' => $current,
            'last' => $last
        ];
        return $kpi;
    }

}
