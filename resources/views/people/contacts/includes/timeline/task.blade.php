<li class="timeline-inverted">
        @if(array_get($post, 'timeline_status') == 'open')
        <div class="timeline-badge info">
            <i class="fa fa-check-square"></i>
        </div>
        @elseif(array_get($post, 'timeline_status') == 'completed')
        <div class="timeline-badge success">
            <i class="fa fa-check-square"></i>
        </div>
        @endif
    
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                <p>
                    {{ array_get($post, 'timeline_chart_of_account') }}
                    @if(array_get($post, 'timeline_status') == 'open')
                        @lang('linked task')
                    @elseif(array_get($post, 'timeline_status') == 'completed')
                    @lang('completed task')
                    @endif
                </p>
            </h4>
            <p>
                <small>
                    <i class="fa fa-clock-o"></i>
                    @if(array_get($post, 'timeline_status') == 'open')
                    {{ displayLocalDateTime(array_get($post, 'timeline_date'))->toDayDateTimeString() }}
                    @elseif(array_get($post, 'timeline_status') == 'completed')
                    {{ displayLocalDateTime(array_get($post, 'timeline_subject'))->toDayDateTimeString() }}
                    @endif
                </small>
            </p>
        </div>
        <div class="timeline-body">
            <p>
                @lang('Due date'):
                {{ humanReadableDate(displayLocalDateTime(array_get($post, 'timeline_date'))->toDateString()) }}
                @php $date = \Carbon\Carbon::parse(array_get($post, 'timeline_amount')) @endphp
                @if($date->endOfDay() != array_get($post, 'timeline_amount'))
                    {{ displayLocalDateTime(array_get($post, 'timeline_amount'))->format('g:i A') }}
                @endif
            </p>
        </div>
    </div>
</li>