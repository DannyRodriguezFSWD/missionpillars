<li class="timeline-inverted">
    <div class="timeline-badge {{$status}}"><i class="fa fa-dollar"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                {{ array_get($contact, 'full_name') }}
            </h4>
            <p>
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ displayLocalDateTime(array_get($post, 'timeline_date'))->toDayDateTimeString() }}
                </small>
            </p>
        </div>
        <div class="timeline-body">
            Made a transaction of ${{ array_get($post, 'timeline_amount') }} for {{ array_get($post, 'timeline_chart_of_account') }}
            @if(strtolower(array_get($post, 'timeline_campaign')) != 'none')
            /{{ array_get($post, 'timeline_campaign') }}
            @endif
        </div>

    </div>
</li>