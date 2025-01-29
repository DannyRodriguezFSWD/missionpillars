@foreach ($sms as $i => $item)
<div class="d-table border-bottom w-100 cursor-pointer px-1 py-2 rounded-lg">
    @if (array_get($item, 'status') === 'received')
    <span class="d-table-cell align-middle" style="width: 20px;">
        <input type="checkbox" id="smsSentAction{{ $i }}" value="{{ array_get($item, 'id') }}" onclick="showSmsActions()" />
    </span>
    
    <span class="d-table-cell" style="width: 60px;">
        @if (is_null(array_get($item, 'from.profile_image')))
        <div class="rounded-circle text-white text-center align-middle d-table-cell" style="width: 50px; height: 50px; font-size: 1.2em; background-color: #{{ stringToColorCode(array_get($item, 'from.full_name')) }}">
            <b>{{ getNameInitials(array_get($item, 'from.full_name')) }}</b>
        </div>
        @else
        <img src="{{ array_get($item, 'from.profile_image_src') }}" class="img-fluid rounded-circle" style="width: 50px;" alt="{{ array_get($item, 'from.full_name') }}" />
        @endif
    </span>
    <span class="d-table-cell align-middle" onclick="loadTexts(this, {{ array_get($item, 'from.id') }});">
        <p class="m-0">
            <b>{{ str_limit(array_get($item, 'from.full_name'), 20) }}</b>
            <span class="pull-right text-muted" title="{{ displayLocalDateTime(array_get($item, 'created_at'))->format("n/d/Y g:i a") }}">{{ array_get($item, 'created_at')->diffForHumans() }}</span>
        </p>
        <p class="mb-0 @if (array_get($item, 'read')) text-muted @else text-info font-weight-bold @endif">
            {{ replaceMergeCodes(str_limit(array_get($item, 'content.content'), 25), array_get($item, 'from')) }}
            @if (!array_get($item, 'read'))
            <span class="pull-right">
                <i class="fa fa-circle"></i>
            </span>
            @endif
        </p>
    </span>
    @else
    <span class="d-table-cell" style="width: 60px;">
        @if (is_null(array_get($item, 'to.profile_image')))
        <div class="rounded-circle text-white text-center align-middle d-table-cell" style="width: 50px; height: 50px; font-size: 1.2em; background-color: #{{ stringToColorCode(array_get($item, 'to.full_name')) }}">
            <b>{{ getNameInitials(array_get($item, 'to.full_name')) }}</b>
        </div>
        @else
        <img src="{{ array_get($item, 'to.profile_image_src') }}" class="img-fluid rounded-circle" style="width: 50px;" alt="{{ array_get($item, 'to.full_name') }}" />
        @endif
    </span>
    <span class="d-table-cell align-middle" onclick="loadTexts(this, {{ array_get($item, 'to.id') }});">
        <p class="m-0">
            <b>{{ str_limit(array_get($item, 'to.full_name'), 20) }}</b>
            <span class="pull-right text-muted" title="{{ displayLocalDateTime(array_get($item, 'created_at'))->format("n/d/Y g:i a") }}">{{ array_get($item, 'created_at')->diffForHumans() }}</span>
        </p>
        <p class="mb-0 text-muted">{{ replaceMergeCodes(str_limit(array_get($item, 'content.content'), 25), array_get($item, 'to')) }}</p>
    </span>
    @endif
</div>
@endforeach
