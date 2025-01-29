<li class="timeline-inverted">
    <div class="timeline-badge">
        <i class="fa fa-list-alt"></i>
    </div>

    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                <p>
                    {{ array_get($post, 'timeline_chart_of_account') }} @lang('form submited')
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
            <p></p>
        </div>
    </div>
</li>