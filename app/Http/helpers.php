<?php

/**
 * Maps data array into Model
 * @param Model $model
 * @param Array $data
 * @return Model
 */
function mapModel($model = null, $data = null) {
    if ($model && $data) {
        foreach ($model->getAttributes(false) as $key) {
            if ($key === $model->getKeyName()) {
                continue;
            }
            if (array_has($data, $key)) {
                array_set($model, $key, $data[$key]);
            }
        }
    }
    return $model;
}

/**
 * Maps data array into Model if the data is empty in the model
 * @param Model $model
 * @param Array $data
 * @return Model
 */
function mapModelIfEmpty($model = null, $data = null) {
    if ($model && $data) {
        foreach ($model->getAttributes(false) as $key) {
            if ($key === $model->getKeyName()) {
                continue;
            }
            if (array_has($data, $key) && empty($model->{$key})) {
                array_set($model, $key, $data[$key]);
            }
        }
    }
    return $model;
}

/**
 * Return month name from month integer [1-12]
 * @param Int $monthNumber
 * @return String
 */
function monthName($monthNumber) {
    if ($monthNumber <= 12) {
        return date("F", mktime(0, 0, 0, $monthNumber, 1));
    }
    return humanReadableDate($monthNumber, true);
}

/**
 * Generates human readable dates
 * @param String $date
 * @param Bool $short
 * @return String
 */
function humanReadableDate($date, $short = false) {
    return $short ? date("D jS, Y", strtotime($date)) : date("F jS, Y", strtotime($date));
}

/**
 * Gets Datetime stored in DB as UTC and shows as Browser's local DateTime
 * @param String $datetime
 * @return Carbon DateTime
 */
function displayLocalDateTime($datetime, $tz = null) {
    $timezone = $tz ? $tz : session('timezone');
    $datetime = \Carbon\Carbon::parse($datetime);
    return $timezone ? $datetime->setTimezone($timezone) : $datetime;
}


/**
 * Shows a date range
 * TODO make $timezone optional and make a way to show local time
 * NOTE: see initial ussage in share_v2.php
 * @param String $start
 * @param String $end
 * @return String
 */
function displayDateTimeRange($start, $end, $allday, $timezone) {
    $start_date = displayLocalDateTime("$start GMT", $timezone)->format('F jS, Y');
    if ($allday) {
        return $start_date;
    }
    $start_time = displayLocalDateTime("$start GMT", $timezone)->format('g:i a');
    $end_date = displayLocalDateTime("$end GMT", $timezone)->format('F jS, Y');
    $end_time = displayLocalDateTime("$end GMT", $timezone)->format('g:i a');

    return ($start_date ==  $end_date)
        ? "$start_date $start_time - $end_time"
        : "$start_date $start_time - $end_date $end_time";
}


/**
 * Gets Datetime entered in inputs and converts it to UTC DateTime
 * @param string $datetime
 * @return Carbon::class
 */
function setUTCDateTime($datetime, $tz = null) {
    $timezone = $tz ? $tz : session('timezone');
    $original = \Carbon\Carbon::parse($datetime)->toDateTimeString();
    $local = displayLocalDateTime($datetime, $timezone)->toDateTimeString();
    $diff = \Carbon\Carbon::parse($original)->diffInMinutes(\Carbon\Carbon::parse($local));
    if (\Carbon\Carbon::parse($datetime)->lt(\Carbon\Carbon::parse($local))){
        $utc = \Carbon\Carbon::parse($datetime)->subMinutes($diff);
    }else{
        $utc = \Carbon\Carbon::parse($datetime)->addMinutes($diff);
    }
    return $utc;
}

function localizeDate($datetime, $startOrEnd, $tz = null)
{
    $extension = $startOrEnd === 'start' ? ' 00:00:00' : ' 23:59:59';
    return $datetime ? setUTCDateTime($datetime.$extension, $tz)->format('Y-m-d H:i:s') : null;
}

function getAvalableTimezones() {
    $tz = [];
    foreach (timezone_abbreviations_list() as $timezone) {
        foreach ($timezone as $val) {
            if (isset($val['timezone_id'])) {
                array_set($tz, $val['timezone_id'], $val['timezone_id']);
            }
        }
    }
    
    asort($tz);
    
    return $tz;
}

function onlyNumbers($data){
    return preg_replace('/[^0-9]/','', $data);
}

function getBaseURL() {
    if (!auth()->user()) trigger_error('getBaseURL requires a logged in session');
    $baseurl = sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain'))."crm/";
    return $baseurl;
}


/**
 * Replaces contact related merge codes
 * @param  [string]  $content
 * @param  [App\Model\Contact]  $contact
 * @param  boolean $usehtml     Optional. If provided and true, will convert applicable replaced characters to HTML
 * @return [string]
 */
function replaceMergeCodes($content, $contact, $usehtml = false) {
    $name = array_get($contact, 'type') === 'organization' ? array_get($contact, 'company') : array_get($contact, 'first_name') . ' ' . array_get($contact, 'last_name');
    $preferred_name = array_get($contact, 'preferred_name');
    $preferred_name_fallback_fullname = $preferred_name ?: $name;

    // TOOD replace 'salutation' in DB and change this
    $title = array_get($contact, 'salutation');
    $salutation = 'Dear ' . $title . ' ' . $preferred_name_fallback_fullname . ',';

    //[:address:]
    $address_str = '';
    if (strpos($content, '[:address:]') !== false) {
        $address = $contact->getMailingAddress();

        if (is_null($address)) {
            $address_str = '';
        } else {
            $address_str = array_get($address, 'mailing_address_1', array_get($address, 'mailing_address_2')) . '<br/> ' . array_get($address, 'city') . ', ' . array_get($address, 'region') . '. ' . array_get($address, 'postal_code');
        }
    }

    $find = [
            '[:name:]',
            '[:first-name:]',
            '[:first_name:]',
            '[:last-name:]',
            '[:organization_name:]',
            '[:ein:]',
            '[:date_today:]',
            '[:contact_id:]',
            '[:salutation:]',
            '[:preferred-name:]',
            '[:address:]',
            '[:preferred-fallback-to-first-last-name:]',
            '[:title:]',
            '[:contact-org-name:]',
            '[:contact-org-title:]',
        ];

    $replace = [
        $name,
        array_get($contact, 'first_name'),
        array_get($contact, 'first_name'),
        array_get($contact, 'last_name'),
        auth()->user()
        ? auth()->user()->tenant->organization
        : $contact->tenant->organization,
        auth()->user()
            ? auth()->user()->tenant->ein
            : $contact->tenant->ein,
        date('M d, Y'),
        array_get($contact, 'id'),
        $salutation,
        $preferred_name,
        $address_str,
        $preferred_name_fallback_fullname,
        $title,
        array_get($contact, 'company'),
        array_get($contact, 'position'),
    ];

    if ($usehtml) for ($i = 0; $i < count($replace); $i++) $replace[$i] = htmlentities($replace[$i]);

    return str_replace($find, $replace, $content);
}

/**
 * Replaces merge codes related to transactions with strings related to transaction data
 * @param  [string]  $content
 * @param  [Collection]  $transactions
 * @param  [string]  $start_date
 * @param  [string]  $end_date
 * @param  boolean $usehtml     Optional. If provided and true, will convert applicable replaced characters to HTML
 * @return [string]
 */
function replaceTransactionCodes($content, $transactions, $lastTransaction, $start_date, $end_date,  $usehtml = false) {
    $total = 0;
    if(!empty($transactions)){
        $total = $transactions->where('transaction.parent_transaction_id', null)->sum('amount');
        $totalSoftCredit = $transactions->where('transaction.parent_transaction_id', '!=', null)->sum('amount');
    }

    $find = [
        '[:start_date:]',
        '[:end_date:]',
        '[:total_amount:]',
        '[:last_transaction_date:]',
        '[:last_transaction_amount:]',
        '[:last_transaction_purpose:]'
    ];

    $totalAmount = '$'.number_format($total, 2);
    
    if ($totalSoftCredit > 0) {
        $totalAmount.= ' and soft credit $'.number_format($totalSoftCredit, 2);
    }
    
    $replace = [
        Carbon\Carbon::parse($start_date)->format('n/j/Y'),
        Carbon\Carbon::parse($end_date)->format('n/j/Y'),
        $totalAmount,
    ];
    
    if ($lastTransaction) {
        $replace[] = displayLocalDateTime(array_get($lastTransaction, 'transaction_initiated_at'))->format('n/j/Y');
        
        if (array_get($lastTransaction, 'splits')) {
            $replace[] = '$'.number_format(array_get($lastTransaction, 'splits')->sum('amount'), 2);
            $replace[] = array_get($lastTransaction, 'splits.0.purpose.name');
        } else {
            $replace[] = '';
            $replace[] = '';
        }
    } else {
        $replace[] = '';
        $replace[] = '';
        $replace[] = '';
    }
    
    for ($i = 0; $i < count($replace); $i++) $replace[$i] = htmlentities($replace[$i]);

    return str_replace($find, $replace, $content);
}

/**
 * Using this if we want to simply remove the mail merge codes.
 * This is used in the email web version / public link
 *
 * @param string $content
 * @return string
 */
function removeMergeCodes($content) {
    $find = [
        '[:name:]',
        '[:first-name:]',
        '[:first_name:]',
        '[:last-name:]',
        '[:organization_name:]',
        '[:ein:]',
        '[:date_today:]',
        '[:contact_id:]',
        '[:salutation:]',
        '[:preferred-name:]',
        '[:address:]',
        '[:preferred-fallback-to-first-last-name:]',
        '[:title:]',
        '[:contact-org-name:]',
        '[:contact-org-title:]',
        '[:start_date:]',
        '[:end_date:]',
        '[:total_amount:]',
        '[:last_transaction_date:]',
        '[:last_transaction_amount:]',
        '[:last_transaction_purpose:]'
    ];

    return str_replace($find, '', $content);
}

/**
 * Replace item_list merge code with transacton list
 * Note: Feb 2021 - changed $pagination to false, causing Print to default to no pagination (https://app.asana.com/0/1192007738839179/1199712701175957/f)
 * @param  [string]  $content
 * @param  [array|object]  $contact
 * @param  boolean $pagination Optional. If true, includes pagination
 * @return [string]
 */
function replaceItemListCode($content, $contact, $pagination = false) {
    $mergecode = '[:item_list:]';
    
    if (strpos($content, $mergecode) !== false):
        $allDonations = array_get($contact, 'donations');
        
        $records_in_first_page = 5; //first page always show 5 records
        $records_in_all_pages = 10;
        $records_per_page = $records_in_first_page;

        // Add normal donations
        $current_record = 1;
        $current_page = 1;
        
        $donations = $allDonations->where('transaction.parent_transaction_id', null)->all();
        $number_of_donations = count($donations);
        $total_pages = ceil(($number_of_donations - $records_in_first_page) / $records_in_all_pages) + 1;

        $table = '<table class="table 1 table-item-list">';
        $tableheading = '<thead>';
        $tableheading .= '<tr>';

        $tableheading .= '<th>Date</th>';
        $tableheading .= '<th>Payment Option</th>';
        $tableheading .= '<th>For</th>';
        $tableheading .= '<th class="text-right">Amount</th>';
        $tableheading .= '</tr>';
        $tableheading .= '</thead>';

        $table .= $tableheading;
        $table .= '<tbody>';

        if(!empty($donations)){
            foreach ($donations as $donation):
                $table .= '<tr>';

                $table .= '<td>' . date('n/j/Y', strtotime(displayLocalDateTime(array_get($donation, 'transaction.transaction_initiated_at')))) . '</td>';

                if (array_get($donation, 'transaction.paymentOption.category') === 'cash') {
                    // TODO consider displaying 'cash'
                    $table .= '<td>&nbsp;</td>';
                } else if (in_array(array_get($donation, 'transaction.paymentOption.category'), ['cc', 'ach', 'check'])) {
                    // TODO consider displaying CC, ACH, Check
                    $table .= '<td>**** ' . array_get($donation, 'transaction.paymentOption.last_four') . '</td>';
                } else {
                    $table .= '<td>&nbsp;</td>';
                }

                $for = array_get($donation, 'campaign_id') > 1 ? array_get($donation, 'campaign.name') : array_get($donation, 'purpose.name');
                $table .= '<td>' . substr($for, 0, 30) . '</td>';
                $table .= '<td class="text-right">$' . number_format(array_get($donation, 'amount', '0.00'), 2, '.', ',') . '</td>';
                $table .= '</tr>';

                if ($pagination && $current_record >= $records_per_page):
                    $current_record = 1;

                    if ($current_page < $total_pages):
                        $table .= '</tbody>';
                        $table .= '</table>';
                        if ($total_pages > 1):
                            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
                        endif;

                        //page break
                        $table .= '<div class="page-break">&nbsp;</div>';

                        $table .= '<table class="table 1">';
                        $table .= $tableheading;
                        $table .= '<tbody>';
                        $current_page++;
                    endif;
                    $records_per_page = $current_page > 1 ? $records_in_all_pages : $records_in_first_page;

                else:
                    $current_record++;
                endif;

            endforeach;
        }


        $table .= '</tbody>';
        $table .= '</table>';
        if ($pagination && $total_pages > 1):
            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
        endif;

        
        // Add soft credits
        $donations = $allDonations->where('transaction.parent_transaction_id', '!=', null)->all();
        
        if (!empty($donations)) {
            $current_record = 1;
            $current_page = 1;
            
            $number_of_donations = count($donations);
            $total_pages = ceil(($number_of_donations - $records_in_first_page) / $records_in_all_pages) + 1;

            $table.= '<p>&nbsp;</p><p>Soft Credits:</p>';
            $table.= '<table class="table 1 table-item-list">';
            $tableheading = '<thead>';
            $tableheading .= '<tr>';

            $tableheading .= '<th>Date</th>';
            $tableheading .= '<th>Payment Option</th>';
            $tableheading .= '<th>For</th>';
            $tableheading .= '<th class="text-right">Amount</th>';
            $tableheading .= '</tr>';
            $tableheading .= '</thead>';

            $table .= $tableheading;
            $table .= '<tbody>';

            foreach ($donations as $donation):
                $table .= '<tr>';

                $table .= '<td>' . date('n/j/Y', strtotime(displayLocalDateTime(array_get($donation, 'transaction.transaction_initiated_at')))) . '</td>';

                if (array_get($donation, 'transaction.paymentOption.category') === 'cash') {
                    // TODO consider displaying 'cash'
                    $table .= '<td>&nbsp;</td>';
                } else if (in_array(array_get($donation, 'transaction.paymentOption.category'), ['cc', 'ach', 'check'])) {
                    // TODO consider displaying CC, ACH, Check
                    $table .= '<td>**** ' . array_get($donation, 'transaction.paymentOption.last_four') . '</td>';
                } else {
                    $table .= '<td>&nbsp;</td>';
                }

                $for = array_get($donation, 'campaign_id') > 1 ? array_get($donation, 'campaign.name') : array_get($donation, 'purpose.name');
                $table .= '<td>' . substr($for, 0, 30) . '</td>';
                $table .= '<td class="text-right">$' . number_format(array_get($donation, 'amount', '0.00'), 2, '.', ',') . '</td>';
                $table .= '</tr>';

                if ($pagination && $current_record >= $records_per_page):
                    $current_record = 1;

                    if ($current_page < $total_pages):
                        $table .= '</tbody>';
                        $table .= '</table>';
                        if ($total_pages > 1):
                            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
                        endif;

                        //page break
                        $table .= '<div class="page-break">&nbsp;</div>';

                        $table .= '<table class="table 1">';
                        $table .= $tableheading;
                        $table .= '<tbody>';
                        $current_page++;
                    endif;
                    $records_per_page = $current_page > 1 ? $records_in_all_pages : $records_in_first_page;

                else:
                    $current_record++;
                endif;

            endforeach;
        }


        $table .= '</tbody>';
        $table .= '</table>';
        if ($pagination && $total_pages > 1):
            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
        endif;
        
        $content = str_replace('<p>' . $mergecode . '</p>', $table, $content);
        $content = str_replace($mergecode, $table, $content);
    endif;
    return $content;
}

/**
 * Replace list_of_donations merge code with transacton list
 * Note: Feb 2021 - changed $pagination to false, causing Print to default to no pagination (https://app.asana.com/0/1192007738839179/1199712701175957/f)
 * @param  [string]  $content
 * @param  [array|object]  $contact
 * @param  boolean $pagination Optional. If true, includes pagination
 * @return [string]
 */
function replaceListOfDonationsCode($content, $contact, $pagination = false) {
    $mergecode = '[:list_of_donations:]';
    
    if (strpos($content, $mergecode) !== false):
        $allDonations = array_get($contact, 'donations');
        
        $records_in_first_page = 5; //first page always show 5 records
        $records_in_all_pages = 10;
        $records_per_page = $records_in_first_page;

        // Add normal donations
        $current_record = 1;
        $current_page = 1;
        
        $donations = $allDonations->where('transaction.parent_transaction_id', null)->all();
        $number_of_donations = count($donations);
        $total_pages = ceil(($number_of_donations - $records_in_first_page) / $records_in_all_pages) + 1;

        $table = '<table class="table 1 table-item-list">';
        $tableheading = '<thead>';
        $tableheading .= '<tr>';

        $tableheading .= '<th>Date</th>';
        $tableheading .= '<th>Payment Option</th>';
        $tableheading .= '<th>For</th>';
        $tableheading .= '<th class="text-right">Amount</th>';
        $tableheading .= '</tr>';
        $tableheading .= '</thead>';

        $table .= $tableheading;
        $table .= '<tbody>';

        if(!empty($donations)){
            foreach ($donations as $donation):
                $table .= '<tr>';

                $table .= '<td>' . date('n/j/Y', strtotime(displayLocalDateTime(array_get($donation, 'transaction.transaction_initiated_at')))) . '</td>';

                if (array_get($donation, 'transaction.paymentOption.category') === 'cash') {
                    // TODO consider displaying 'cash'
                    $table .= '<td>&nbsp;</td>';
                } else if (in_array(array_get($donation, 'transaction.paymentOption.category'), ['cc', 'ach', 'check'])) {
                    // TODO consider displaying CC, ACH, Check
                    $table .= '<td>**** ' . array_get($donation, 'transaction.paymentOption.last_four') . '</td>';
                } else {
                    $table .= '<td>&nbsp;</td>';
                }

                $for = array_get($donation, 'campaign_id') > 1 ? array_get($donation, 'campaign.name') : array_get($donation, 'purpose.name');
                $table .= '<td>' . substr($for, 0, 30) . '</td>';
                $table .= '<td class="text-right">$' . number_format(array_get($donation, 'amount', '0.00'), 2, '.', ',') . '</td>';
                $table .= '</tr>';

                if ($pagination && $current_record >= $records_per_page):
                    $current_record = 1;

                    if ($current_page < $total_pages):
                        $table .= '</tbody>';
                        $table .= '</table>';
                        if ($total_pages > 1):
                            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
                        endif;

                        //page break
                        $table .= '<div class="page-break">&nbsp;</div>';

                        $table .= '<table class="table 1">';
                        $table .= $tableheading;
                        $table .= '<tbody>';
                        $current_page++;
                    endif;
                    $records_per_page = $current_page > 1 ? $records_in_all_pages : $records_in_first_page;

                else:
                    $current_record++;
                endif;

            endforeach;
        }


        $table .= '</tbody>';
        $table .= '</table>';
        if ($pagination && $total_pages > 1):
            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
        endif;

        
        // Add soft credits
        $donations = $allDonations->where('transaction.parent_transaction_id', '!=', null)->all();
        
        if (!empty($donations)) {
            $current_record = 1;
            $current_page = 1;
            
            $number_of_donations = count($donations);
            $total_pages = ceil(($number_of_donations - $records_in_first_page) / $records_in_all_pages) + 1;

            $table.= '<p>&nbsp;</p><p>Soft Credits:</p>';
            $table.= '<table class="table 1 table-item-list">';
            $tableheading = '<thead>';
            $tableheading .= '<tr>';

            $tableheading .= '<th>Date</th>';
            $tableheading .= '<th>Payment Option</th>';
            $tableheading .= '<th>For</th>';
            $tableheading .= '<th class="text-right">Amount</th>';
            $tableheading .= '</tr>';
            $tableheading .= '</thead>';

            $table .= $tableheading;
            $table .= '<tbody>';

            foreach ($donations as $donation):
                $table .= '<tr>';

                $table .= '<td>' . date('n/j/Y', strtotime(displayLocalDateTime(array_get($donation, 'transaction.transaction_initiated_at')))) . '</td>';

                if (array_get($donation, 'transaction.paymentOption.category') === 'cash') {
                    // TODO consider displaying 'cash'
                    $table .= '<td>&nbsp;</td>';
                } else if (in_array(array_get($donation, 'transaction.paymentOption.category'), ['cc', 'ach', 'check'])) {
                    // TODO consider displaying CC, ACH, Check
                    $table .= '<td>**** ' . array_get($donation, 'transaction.paymentOption.last_four') . '</td>';
                } else {
                    $table .= '<td>&nbsp;</td>';
                }

                $for = array_get($donation, 'campaign_id') > 1 ? array_get($donation, 'campaign.name') : array_get($donation, 'purpose.name');
                $table .= '<td>' . substr($for, 0, 30) . '</td>';
                $table .= '<td class="text-right">$' . number_format(array_get($donation, 'amount', '0.00'), 2, '.', ',') . '</td>';
                $table .= '</tr>';

                if ($pagination && $current_record >= $records_per_page):
                    $current_record = 1;

                    if ($current_page < $total_pages):
                        $table .= '</tbody>';
                        $table .= '</table>';
                        if ($total_pages > 1):
                            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
                        endif;

                        //page break
                        $table .= '<div class="page-break">&nbsp;</div>';

                        $table .= '<table class="table 1">';
                        $table .= $tableheading;
                        $table .= '<tbody>';
                        $current_page++;
                    endif;
                    $records_per_page = $current_page > 1 ? $records_in_all_pages : $records_in_first_page;

                else:
                    $current_record++;
                endif;

            endforeach;
        }


        $table .= '</tbody>';
        $table .= '</table>';
        if ($pagination && $total_pages > 1):
            $table .= '<p class="text-right">Page ' . $current_page . ' of ' . $total_pages . '</p>';
        endif;
        
        $content = str_replace('<p>' . $mergecode . '</p>', $table, $content);
        $content = str_replace($mergecode, $table, $content);
    endif;
    return $content;
}

/**
 * Appends a donation property to a contact object withing the specified date range
 * @param  [Contact] $contact  - this will be a Contact either with a pivot from the Communication::recipients relation or select columns from Mailgun:Send
 * @param  [string] $start
 * @param  [string] $end
 */
function appendTransactionsToContact(&$contact, $start, $end, $filters = [], $timezone = null)
{
    appendAcknowledgedThresholdToFilter($contact, $filters);

    $start = localizeDate($start, 'start', $timezone);
    $end = localizeDate($end, 'end', $timezone);
    
    $splits = App\Models\TransactionSplit::with([
        'transaction.paymentOption',
        'purpose','campaign'])
    ->whereHas('transaction', function($query) use($contact, $start, $end, $filters) {
        $query->where('transaction_initiated_at', '>=', $start)->where('transaction_initiated_at', '<=', $end)
        ->where('contact_id', $contact->cid ?: $contact->id); // HACK Send::queue renames the contact_id
        if (array_key_exists('acknowledged',$filters)) {
            $query->acknowledged($filters['acknowledged'], $filters['acknowledged_threshold']);
        }
        if (array_key_exists('completed', $filters)) {
            $query->completed($filters['completed']);
        }
        $query->where(function ($q) {
            $q->where('tax_deductible', true)->orWhereNotNull('parent_transaction_id');
        });
    });
    if (array_key_exists('tagged_with_ids', $filters)) {
        $splits->taggedWithIds($filters['tagged_with_ids']);
    }
    if (array_key_exists('not_tagged_with_ids', $filters)) {
        $splits->notTaggedWithIds($filters['not_tagged_with_ids']);
    }
    
    $splits->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id');
    
    $contact->donations = $splits->orderBy('transactions.transaction_initiated_at')->get();
    
    $contact->lastTransaction = App\Models\Transaction::with('splits.purpose')
            ->where('contact_id', $contact->cid ?: $contact->id)
            ->completed()
            ->orderBy('transaction_initiated_at', 'desc')
            ->first();
}

/**
 * Adds a threshold for acknowledged to be used when filtering. see Transaction's scopes
 * @param  [Contact] $recipient - this will be a Contact either with a pivot from the Communication::recipients relation or select columns from Mailgun:Send
 * @param  [array] $filters
 */
function appendAcknowledgedThresholdToFilter($recipient, &$filters) {
    if (in_array('acknowledged', $filters)) {
        if (isset($recipient->email_queued_at)) {
            $filters['acknowledged_threshold'] = $recipient->email_queued_at;
        }
        else {
            $filters['acknowledged_threshold'] = $recipient->pivot->created_at->setTimezone('UTC')->format('Y-m-d G:i:s');
        }

    }
}

function dbDateFormat($date) {
    $date = strtotime($date);
    if (!$date) return null;
    return date('Y-m-d', $date);
}


/**
 * Returns a css font-size definition based on the length of a string
 * @param  [string]  $string
 * @param  integer $threshold   Optional. Default = 15. If the length is above this number of characters the font-size shrinks
 * @param  float   $multiplier  Optional. Default = 0.1 The amount of vw units to scale a string that is twice the threshold
 * @param  float   $defaultsize Optional. Default = 2.0 The default font-size in vw
 * @return string               e.g., "font-size: .8vw"
 */
function cssDynamicFontSize($string, $threshold = 15, $multiplier = 0.1, $defaultsize = 1.5) {
    $length = strlen($string);
    return 'font-size: ' .
    ($length < $threshold ? $defaultsize : ($defaultsize - $multiplier * ($length - $threshold) / $threshold))
    . "vw;";
}

function str_limit_middle($string, $limit, $replace = "...") {
    if (strlen($string) <= $limit) return $string;

    return str_limit($string, $limit/2 - 1,$replace.substr($string,-($limit/2)));
}

function to_currency($money)
{
    $formatted = number_format(sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $money)), 2);
    return $money < 0 ? "({$formatted})" : "{$formatted}";
}



function html_matchImgTag($content)
{
    preg_match_all('/<img[^>]+>/i',$content, $images);
    return $images;
}



function html_matchSrcAttr($img)
{
    preg_match( '@src="([^"]+)"@' , $img, $match );
    $src = array_pop($match);
    return $src;
}



function html_extractMime($src)
{
    $filename = $mime = null;
    preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
    if (!array_key_exists('mime', $groups)) return compact('filename','mime');
    $mimetype = $groups['mime'];
    //dd($mimetype);
    $filename = \Ramsey\Uuid\Uuid::uuid4() . '.' . $mimetype;

    $mime = 'data:image/jpeg;base64,';
    switch (strtolower($mimetype)) {
        case 'gif':
            $mime = 'data:image/gif;base64,';
            break;
        case 'png':
            $mime = 'data:image/png;base64,';
            break;
        default :
            break;
    }

    return compact('filename','mime');
}

function checkAndDeleteFile($file)
{
    if (is_file($file)) return unlink($file);
    return false;
}

function nullIfEmpty($value)
{
    return empty($value) ? null : $value;
}

function getNameInitials($name, $maxChars = 2)
{
    if (empty($name)) {
        return $name;
    }
    
    $ex = explode(' ', $name);
    $initials = '';   
    
    for ($i = 0; $i < $maxChars; $i++) {
        $initials.= strtoupper(substr($ex[$i], 0, 1));
    }
    
    return $initials;
}

function randomColor() 
{
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

function stringToColorCode($str) 
{
    return substr(md5($str), 0, 6);
}

function getCsvData($file)
{
    $headers = [];
    $data = [];
    $i = 0;    
    
    if (($handle = fopen($file, "r")) !== false) {
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            if ($i === 0) {
                foreach ($row as $val) {
                    $headers[] = strtolower(str_replace(' ', '_', $val));
                }
            } else {
                $element = [];
                
                foreach ($row as $key => $val) {
                    $element[$headers[$key]] = $val;
                }
                
                $data[] = $element;
            }
            $i++;
        }
        fclose($handle);
    }
    
    return $data;
}

function getQuery($builder)
{
    $addSlashes = str_replace('?', "'?'", $builder->toSql());
    return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
}

function bytesToSize($bytes) 
{ 
    $units = ['B', 'KB', 'MB', 'GB', 'TB']; 

    if ($bytes == 0) return '0 Byte';
    
    $i = floor(log($bytes) / log(1024));
    return round($bytes / pow(1024, $i), 2).' '.$units[$i];
} 

function toCurrencyReverse($string)
{
    return str_replace(['$ ', ','], '', $string);
}

/**
 * Stores data in db table debug_dumps
 * @param type $data
 */
function debugDumpHelper($data)
{
    $dump = new \App\Models\DebugDump;
    $dump->dump = json_encode($data);
    $dump->save();
}

function stripAllHtmlTags($html)
{
    $bodyPosition = strpos($html, '<body');
    
    if ($bodyPosition) {
        $html = substr($html, $bodyPosition);
    }
    
    return trim(preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html)))));
}

function verifyCaptcha($captcha) 
{
    $url  = "https://www.google.com/recaptcha/api/siteverify";
    $url .= "?secret="  .urlencode(stripslashes(env('RECAPTCHA_SECRET_KEY')));
    $url .= "&response=".urlencode(stripslashes($captcha));

    $response = file_get_contents($url);
    return json_decode($response);
}
