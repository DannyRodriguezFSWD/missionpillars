<p>@lang('Hello') {{ array_get($task, 'assignedTo.full_name') }},</p>

<p>@lang('The following task has been assigned to you').</p>

<p>
    <b>@lang('Task name')</b>: {{ array_get($task, 'name') }}<br>
    <b>@lang('Contact name')</b>: {{ array_get($task, 'linkedTo.full_name') }}<br>
    <b>@lang('Due date')</b>: {{ humanReadableDate(displayLocalDateTime(array_get($task, 'due'))->toDateString()) }} @if(array_get($task, 'show_time')) {{ displayLocalDateTime(array_get($task, 'due'))->format('g:i A') }} @endif
</p>

<p>@lang('Thank you'),</p>

<p>{{ array_get($task, 'assignedTo.tenant.organization') }}</p>
