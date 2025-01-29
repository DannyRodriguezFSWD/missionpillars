<li class="timeline-inverted">
    <div class="timeline-badge"><i class="fa fa-print"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                A printout with the label: "{{ array_get($post, 'timeline_subject') }}" was made
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
            <button class="btn btn-secondary btn-sm" type="button" onclick="viewTimelinePrint({{ array_get($post, 'timeline_id') }});">
                <i class="fa fa-eye"></i> View Printout
            </button>
            @endif
        </div>
    </div>
</li>