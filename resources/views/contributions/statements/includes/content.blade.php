<?php

foreach ($contacts as $contact):
    $content = replaceMergeCodes($statement->print_content, $contact);
    if ($statement->include_transactions) {
        $content = replaceTransactionCodes(
            $content, $contact->donations, array_get($contact, 'lastTransaction'),
            $statement->transaction_start_date ?: $statement->start_date,
            $statement->transaction_end_date ?: $statement->end_date );
        $content = replaceItemListCode($content, $contact);
        $content = replaceListOfDonationsCode($content, $contact);
    }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="document-page">{!! $content !!}</div>
        </div>
    </div>
    
    <div class="page-break"></div>
    
    <?php
endforeach;
?>
