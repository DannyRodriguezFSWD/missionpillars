@foreach ($sms as $item)
<li class="{{ array_get($item, 'status') == 'received' ? 'timeline-inverted' : 'timeline-default' }}">
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h6 class="timeline-title mb-0">
                {{ array_get($item, 'from.first_name') }}
                {{ array_get($item, 'from.last_name') }}
            </h6>
            <p class="mb-0">
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ displayLocalDateTime(array_get($item, 'content.created_at'))->toDayDateTimeString() }}
                </small>
            </p>
            <p class="mb-0">
                <small>
                    <i class="fa fa-bolt"></i>
                    Status: <span class="font-weight-bold
                        @if (array_get($item, 'status') === 'sent' || array_get($item, 'status') === 'delivered' || array_get($item, 'status') === 'received')
                        text-success
                        @elseif (array_get($item, 'status') === 'error')
                        text-danger
                        @else
                        text-warning
                        @endif
                    ">{{ ucfirst(array_get($item, 'status')) }}</span>
                </small>
            </p>
            @if (array_get($item, 'status') === 'received')
            <p>
                <small>
                    <i class="fa fa-mobile"></i> To:
                    {{ array_get($item, 'phone_number_to') }}
                </small>
            </p>
            @else
            <p>
                <small>
                    <i class="fa fa-mobile"></i> From:
                    {{ array_get($item, 'phone_number_from') }}
                </small>
            </p>
            @endif
        </div>
        <div class="timeline-body">
            <p>{{ replaceMergeCodes(array_get($item, 'content.content'), array_get($item, 'to')) }}</p>
            @if (array_get($item, 'status') == 'error')
            @php $message = explode(':', array_get($item, 'message', '')) @endphp
            <p class="text-danger">
                Error: 
                {{ array_get($message, '1', "Can't send sms, please check contact's mobile phone number") }}
            </p>
            @endif
        </div>
    </div>
</li>
@endforeach
