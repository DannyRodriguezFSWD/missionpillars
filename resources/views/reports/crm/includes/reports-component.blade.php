<div id="crm-reports-viewport">
    <crm-reports-components 
    reports-list="hide"
    title="{{ array_get($report, 'name') }}"
    v-bind:id="{{ array_get($report, 'id') }}"
    base="{{ url('crm') }}"
    from="{{ $from }}"
    to="{{ $to }}"
    from2="{{ $from2 }}"
    to2="{{ $to2 }}"
    v-bind:amount_ranges="{{ $amount_ranges }}"
    in_tags="{{ $in_tags }}"
    out_tags="{{ $out_tags }}"
    v-bind:in_list="{{ $list }}"
    ></crm-reports-components>
</div>