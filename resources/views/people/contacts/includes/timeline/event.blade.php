<li class="timeline-inverted">
    <div class="timeline-badge {{$status}}"><i class="fa fa-calendar"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                <p>
                    Signed up to {{ array_get($post, 'timeline_chart_of_account') }} event
                </p>
            </h4>
            <p>
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ displayLocalDateTime(array_get($post, 'timeline_date'))->toDayDateTimeString() }}
                </small>
            </p>
        </div>
        <div class="timeline-body">

        </div>
    </div>
</li>