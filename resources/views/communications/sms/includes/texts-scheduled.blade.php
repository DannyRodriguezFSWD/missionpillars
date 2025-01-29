@foreach ($sms as $i => $item)
<div class="d-table border-bottom w-100 cursor-pointer px-1 py-2 rounded-lg">
    <span class="d-table-cell align-middle" onclick="loadSchedule(this, {{ array_get($item, 'id') }});">
        <p class="m-0">
            <b>{{ str_limit(array_get($item, 'list.name', 'Everyone'), 20) }}</b>
            <span class="pull-right text-muted">{{ displayLocalDateTime(array_get($item, 'time_scheduled'))->format("n/d/Y g:i a") }}</span>
        </p>
        <p class="mb-0">
            {{ str_limit(array_get($item, 'content'), 25) }}
        </p>
    </span>
</div>
@endforeach
