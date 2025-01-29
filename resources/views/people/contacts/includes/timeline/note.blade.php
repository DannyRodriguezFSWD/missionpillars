<li class="timeline-inverted">
    <div class="timeline-badge warning">
        <i class="fa fa-sticky-note-o"></i>
    </div>

    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                <p>
                    {{ array_get($post, 'timeline_chart_of_account') }}
                </p>
            </h4>
            <p>
                <small>
                    <i class="fa fa-clock-o"></i>
                    {{ date('D, M j, Y', strtotime(array_get($post, 'timeline_date'))) }}
                </small>
            </p>
        </div>
        <div class="timeline-body">
            <p>
                {{ array_get($post, 'timeline_subject') }}
            </p>
        </div>
    </div>
</li>