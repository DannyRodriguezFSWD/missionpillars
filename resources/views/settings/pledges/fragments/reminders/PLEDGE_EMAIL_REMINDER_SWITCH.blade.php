<div class="form-group row">    
    {{ Form::label('PLEDGE_EMAIL_REMINDER_SWITCH', __('Turn On/Off Email Reminders'), ['class' => 'col-md-10 col-form-label']) }}
    <div class="col-sm-2 text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            @if( is_null(array_get($settings, '0.value')) )
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH" name="PLEDGE_EMAIL_REMINDER_SWITCH" checked="" type="checkbox" class="c-switch-input" value="{{ array_get($settings, '0.id') }}">
            @else
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH" name="PLEDGE_EMAIL_REMINDER_SWITCH" {{ array_get($settings, '0.value.value') === '1' ? 'checked' : '' }} type="checkbox" class="c-switch-input" value="{{ array_get($settings, '0.id') }}">
            @endif
            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>

        </label>
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

        $('#PLEDGE_EMAIL_REMINDER_SWITCH').on('change', function (e) {
            var checked = $(this).prop('checked');
            if (checked) {
                $('.email_reminder_input').prop('disabled', false).removeClass('disabled');
            } else {
                $('.email_reminder_input').prop('disabled', true).addClass('disabled');
            }

            var data = {
                id: $(this).val(),
                setting: $(this).attr('name'),
                checked: checked,
                PLEDGE_EMAIL_REMINDER_TEXT_EVERY: $('input[name="PLEDGE_EMAIL_REMINDER_TEXT_EVERY"]').val(),
                PLEDGE_EMAIL_REMINDER_TEXT_STARTING: $('input[name="PLEDGE_EMAIL_REMINDER_TEXT_STARTING"]').val()
            };
            
            var url = "{{ route('settings.pledges.store') }}";
            $.post(url, data).done(function (data) {
                if (checked) {
                    $('.email_reminder_input').prop('disabled', false).removeClass('disabled');
                } else {
                    $('.email_reminder_input').prop('disabled', true).addClass('disabled');
                }
            }).fail(function (data) {
                console.log(data.responseText);
                Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            });

        });
        
        $('.email_reminder_input').on('keyup', function(e){
            $('#PLEDGE_EMAIL_REMINDER_SWITCH').change();
        });
        
        $('input[name="PLEDGE_EMAIL_REMINDER_SWITCH"]').change();
        
        $('.email_reminder_input').on('change', function(e){
            $('#PLEDGE_EMAIL_REMINDER_SWITCH').change();
        });

    })();
</script>
@endpush