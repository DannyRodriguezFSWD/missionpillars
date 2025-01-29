<li class="timeline-inverted">
    <div class="timeline-badge {{$status}}"><i class="fa fa-envelope-o"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                An email with the subject: "{{ array_get($post, 'timeline_subject') }}"
                @if(strtotime(array_get($post, 'timeline_date')) > strtotime(date('Y-m-d H:i:s')))
                is scheduled for
                @else
                was sent
                @endif
            </h4>
            <p>
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ displayLocalDateTime(array_get($post, 'timeline_date'))->toDayDateTimeString() }}
                </small>
            </p>
        </div>
        <div class="timeline-body">
            @if(array_get($post, 'timeline_group'))
            <button class="btn btn-secondary btn-sm" type="button" onclick="viewTimelineEmail({{ array_get($post, 'timeline_id') }});">
                <i class="fa fa-eye"></i> View Email
            </button>
            @endif
        </div>
    </div>
</li>
