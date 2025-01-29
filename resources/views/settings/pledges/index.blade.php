@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('settings.pledges.index') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="text-center col-sm-12">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> @lang('Back')
                </a>
                <div>@lang('Pledge Settings')</div>
            </div>
        </div>
    </div>
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#settings-1" role="tab" aria-controls="entries">
                    <i class="fa fa-envelope"></i> @lang('Reminders')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#settings-2" role="tab" aria-controls="entries">
                    <i class="fa fa-bell"></i> @lang('Notifications')
                </a>
            </li>
            
        </ul>
    </div>
    <div class="card-body">
        
        <div class="tab-content">
            
            <div class="tab-pane active" id="settings-1" role="tabpanel">
                @includeIf('settings.pledges.fragments.reminders.PLEDGE_EMAIL_REMINDER_SWITCH')
                @includeIf('settings.pledges.fragments.reminders.PLEDGE_EMAIL_REMINDER_TEXT_EVERY')
            </div>
            
            <div class="tab-pane" id="settings-2" role="tabpanel">
                @includeIf('settings.pledges.fragments.notifications.PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT')
                @includeIf('settings.pledges.fragments.notifications.PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN')
                @includeIf('settings.pledges.fragments.notifications.PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_CONTACT')
                @includeIf('settings.pledges.fragments.notifications.PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN')
            </div>
            
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    (function () {

        $('.pledge-notification-switch').on('change', function (e) {
            var checked = $(this).prop('checked');
            var data = {
                id: $(this).val(),
                setting: $(this).attr('name'),
                checked: checked
            };
            
            var url = "{{ route('settings.pledges.store') }}";
            $.post(url, data).done(function (data) {
                
            }).fail(function (data) {
                console.log(data.responseText);
                Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            });

        });

        $('.pledge-notification-switch').change();

    })();
</script>
@endpush

@endsection