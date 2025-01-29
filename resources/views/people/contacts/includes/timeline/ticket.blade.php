<li class="timeline-inverted">
    @if(array_get($post, 'timeline_subject') == 0)
    <div class="timeline-badge info">
        <i class="fa fa-ticket"></i>
    </div>
    @else
    <div class="timeline-badge success">
        <i class="fa fa-ticket"></i>
    </div>
    @endif
    
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">
                @if(array_get($post, 'timeline_subject') == 0)
                <p>Free ticket</p>
                @else
                <p>Ticket purchase</p>
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
            @if(array_get($post, 'timeline_subject') == 0)
                <p>Reserved {{ array_get($post, 'timeline_chart_of_account') }} for {{ array_get($post, 'timeline_campaign') }}</p>
                @else
                <p>Purchased a ticket {{ array_get($post, 'timeline_chart_of_account') }} for {{ array_get($post, 'timeline_campaign') }}</p>
                @endif
        </div>
    </div>
</li>