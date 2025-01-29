<?php

namespace App\Classes\Statements;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

/**
 * Description of Subdomains
 *
 * @author josemiguel
 */
class Statement {

    /**
     * @deprecated not used anymore
     */
    public static function getAllDonorsData($start, $end, $report, $statement) {
        $not_in_statement_but_has_donations = [];
        $not_in_statement = [];
        $groupBy = [DB::raw('contacts.id')];
        $select = [
            'contacts.id',
            'contacts.first_name',
            'contacts.last_name',
            'contacts.email_1',
            DB::raw('sum(transaction_splits.amount) as total_amount'),
            DB::raw('count(transaction_splits.id) as total_records')
        ];

        if ($report) {
            $contacts = $statement->contacts;
            $not_in_statement = Contact::whereNotIn('id', array_pluck($contacts, 'id'))->get();

            $status = ['complete'];
            $not_in_statement_but_has_donations = Contact::join('transactions', 'transactions.contact_id', '=', 'contacts.id')
                    ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
                    ->join('transaction_templates', 'transactions.transaction_template_id', '=', 'transaction_templates.id')
                    ->select($select)
                    ->where('transactions.tenant_id', array_get(auth()->user(), 'tenant.id'))
                    ->where('transactions.tax_deductible', true)
                    ->whereIn('transactions.status', $status)
                    ->whereIn('contacts.id', array_pluck($not_in_statement, 'id'))
                    ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()])
                    ->groupBy($groupBy)
                    ->get();
        } else {
            $contacts = Contact::join('transactions', 'transactions.contact_id', '=', 'contacts.id')
                    ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
                    ->join('transaction_templates', 'transactions.transaction_template_id', '=', 'transaction_templates.id')
                    ->select($select)
                    ->where([
                        ['transactions.tenant_id', '=', array_get(auth()->user(), 'tenant.id')],
                        ['transactions.tax_deductible', '=', true],
                        ['transactions.status', '=', 'complete']
                    ])
                    ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()])
                    ->groupBy($groupBy)
                    ->get();
        }

        return [
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement
        ];
    }

    public static function getDonorsWithAddress($start, $end, $report, $statement) {
        $not_in_statement_but_has_donations = null;
        $not_in_statement = [];
        if ((int)array_get($statement, 'use_date_range') == 0) {
            $contacts = Contact::whereHas('addressInstance', function($query) {
                        $query->where([ ['relation_type', '=', Contact::class] ])
                                ->where(function($q){
                                    $q->whereNotNull('country')->orWhereNotNull('country_id');
                                })
                                ->whereNotNull('mailing_address_1')
                                ->whereNotNull('city')
                                ->whereNotNull('region')
                                ->whereNotNull('postal_code');
                    })->orderBy('first_name')->orderBy('last_name')->get();
                    
            $not_in_statement = Contact::doesntHave('addressInstance')->orderBy('first_name')->orderBy('last_name')->get();
            $not_in_statement_but_has_donations = null;
        } else {
            $contacts = Contact::with(['transactions', 'transactions.splits'])->whereHas('transactions', function($q) use($start, $end){
                $q->whereHas('splits', function($query){
                    $query->where('tax_deductible', true)
                        ->where('amount', '!=', 0)
                        ->whereNotNull('amount');
                })->where('status', 'complete')
                ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->whereHas('addressInstance', function($q){
                $q->where('relation_type', Contact::class)
                    ->whereNotNull('mailing_address_1')
                    ->whereNotNull('city')
                    ->whereNotNull('region')
                    ->where(function($query){
                        $query->whereNotNull('country')->orWhereNotNull('country_id');
                    })
                    ->whereNotNull('postal_code');
            })->orderBy('first_name')->orderBy('last_name')->get();
            
            $diff = array_diff(array_pluck(Contact::all(), 'id'), array_pluck($contacts, 'id'));
            $not_in_statement = Contact::whereIn('id', $diff)->orderBy('first_name')->orderBy('last_name')->get();

            $without_address = Contact::doesntHave('addressInstance')
                ->whereHas('transactions', function($q) use($start, $end){
                    $q->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
                })->orderBy('first_name')->orderBy('last_name')->get();

            $with_missing_data_in_address = Contact::whereHas('addressInstance', function($q){
                $q->whereNull('mailing_address_1')
                    ->orWhereNull('city')
                    ->orWhereNull('region')
                    ->where(function($query){
                        $query->whereNull('country')->whereNull('country_id');
                    })
                    ->orWhereNull('postal_code');
            })->whereHas('transactions', function($q) use($start, $end){
                    $q->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
                })->orderBy('first_name')->orderBy('last_name')->get();

            $diff = array_merge(array_pluck($without_address, 'id'), array_pluck($with_missing_data_in_address, 'id'));

            $not_in_statement_but_has_donations = Contact::whereHas('transactions', function($q) use($diff){
                $q->whereIn('contact_id', $diff);
            })->orderBy('first_name')->orderBy('last_name')->get();
        }

        return [
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement
        ];
    }

    public static function getDonorsWithoutEmailAddress($start, $end, $report, $statement) {
        $not_in_statement_but_has_donations = null;
        $not_in_statement = [];
        
        if (array_get($statement, 'use_date_range') == 0) {
            $contacts = Contact::where(function($query){
                $query->whereNull('email_1')->orWhere('email_1', '');
            })->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement = Contact::whereNotIn('id', array_pluck($contacts, 'id'))->orderBy('first_name')->orderBy('last_name')->get();
            $not_in_statement_but_has_donations = null;
        } else {
            $contacts = Contact::with(['transactions', 'transactions.splits'])->whereHas('transactions', function($q) use($start, $end){
                $q->whereHas('splits', function($query){
                    $query->where('tax_deductible', true)
                        ->where('amount', '!=', 0)
                        ->whereNotNull('amount');
                })->where('status', 'complete')
                ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->whereNull('contacts.email_1')
            ->orWhere('contacts.email_1', '')->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement = Contact::whereNotIn('id', array_pluck($contacts, 'id'))->orderBy('first_name')->orderBy('last_name')->get();
            $not_in_statement_but_has_donations = Contact::whereHas('transactions', function($q) use($start, $end){
                $q->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->whereNotNull('contacts.email_1')
            ->orWhere('contacts.email_1', '!=', '')->orderBy('first_name')->orderBy('last_name')->get();
        }

        return [
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement
        ];
    }

    public static function getDonorsMarkedPaperStatement($start, $end, $report, $statement) {
        $not_in_statement_but_has_donations = null;
        $not_in_statement = [];
        
        if (array_get($statement, 'use_date_range') == 0) {
            $contacts = Contact::where('send_paper_contribution_statement', true)->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement_but_has_donations = null;
            $not_in_statement = Contact::where('send_paper_contribution_statement', false)->orWhereNull('send_paper_contribution_statement')->orderBy('first_name')->orderBy('last_name')->get();
        } else {
            $contacts = Contact::with(['transactions', 'transactions.splits'])->whereHas('transactions', function($q) use($start, $end){
                $q->whereHas('splits', function($query){
                    $query->where('tax_deductible', true)
                        ->where('amount', '!=', 0)
                        ->whereNotNull('amount');
                })->where('status', 'complete')
                ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->where('send_paper_contribution_statement', true)->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement_but_has_donations = Contact::whereHas('transactions', function($q) use($start, $end){
                $q->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->where(function($q){
                $q->where('send_paper_contribution_statement', false)
                ->orWhereNull('send_paper_contribution_statement');
            })->orderBy('first_name')->orderBy('last_name')->get();
            $not_in_statement = Contact::where('send_paper_contribution_statement', false)->orWhereNull('send_paper_contribution_statement')->orderBy('first_name')->orderBy('last_name')->get();        
        }

        return [
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement
        ];
    }

    public static function getDonorsNotMarkedPaperStatement($start, $end, $report, $statement) {
        $not_in_statement_but_has_donations = null;
        $not_in_statement = [];
        $groupBy = [DB::raw('contacts.id')];
        $select = [
            'contacts.id',
            'contacts.first_name',
            'contacts.last_name',
            'contacts.email_1',
            DB::raw('sum(transaction_splits.amount) as total_amount'),
            DB::raw('count(transaction_splits.id) as total_records')
        ];

        if (array_get($statement, 'use_date_range') == 0) {
            $contacts = Contact::where('send_paper_contribution_statement', false)
                ->orWhereNull('send_paper_contribution_statement')
                ->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement_but_has_donations = null;
            $not_in_statement = Contact::where('send_paper_contribution_statement', true)->orderBy('first_name')->orderBy('last_name')->get();
        } else {
            $contacts = Contact::with(['transactions', 'transactions.splits'])->whereHas('transactions', function($q) use($start, $end){
                $q->whereHas('splits', function($query){
                    $query->where('tax_deductible', true)
                        ->where('amount', '!=', 0)
                        ->whereNotNull('amount');
                })->where('status', 'complete')
                ->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->where('send_paper_contribution_statement', false)
            ->orWhereNull('send_paper_contribution_statement')->orderBy('first_name')->orderBy('last_name')->get();
            
            $not_in_statement_but_has_donations = Contact::whereHas('transactions', function($q) use($start, $end){
                $q->whereBetween('transaction_initiated_at', [$start->startOfDay(), $end->endOfDay()]);
            })->where(function($q){
                $q->where('send_paper_contribution_statement', true);
            })->orderBy('first_name')->orderBy('last_name')->get();
            $not_in_statement = Contact::where('send_paper_contribution_statement', true)->orderBy('first_name')->orderBy('last_name')->get();
        }

        return [
            'contacts' => $contacts,
            'not_in_statement_but_has_donations' => $not_in_statement_but_has_donations,
            'not_in_statement' => $not_in_statement
        ];
    }

}
