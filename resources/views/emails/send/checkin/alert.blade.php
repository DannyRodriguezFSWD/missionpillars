<h4>@lang('Hello') {{ array_get($event, 'template.managers.0.full_name') }},</h4>

<p>@lang('This is an automated email that is sent as a reminder to checkin people for the event') <b>{{ array_get($event, 'template.name') }}</b>.</p>

<p>@lang('Please follow the link below and check in the people that attended the event').</p>

<p><a href="{{ array_get($event, 'checkin_url') }}" target="_blank" class="button button-green">@lang('Click here to checkin people').</a></p>

<p>@lang('Thank you'),</p>

<p>{{ array_get($event, 'template.managers.0.tenant.organization') }}</p>
