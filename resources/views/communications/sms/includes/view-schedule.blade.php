<div class="p-5">
    <div class="alert alert-info">
        <h4>SMS scheduled for list: {{ array_get($sms, 'list.name', 'Everyone') }}</h4>
        <h5>Scheduled to send on: {{ displayLocalDateTime(array_get($sms, 'time_scheduled'))->format("n/d/Y g:i a") }}</h5>
        <h5>Number of contacts: {{ count(array_get($sms, 'sent')) }}</h5>
        <h5>Content:</h5>
        <p>{{ array_get($sms, 'content') }}</p>
    </div>
    
    <button class="btn btn-danger" data-url="{{ route('sms.destroy', $sms->id) }}" onclick="cancelSchedule(this);">
        <i class="fa fa-trash"></i> Cancel
    </button>
</div>