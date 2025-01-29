<div class="form-group row">    
    {{ Form::label('PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT', __('Send thank you email to donor when when they make a payment toward their pledge'), ['class' => 'col-md-10 col-form-label']) }}
    <div class="col-sm-2 text-right">
        <label class="c-switch c-switch-label  c- c-switch-primary">
            @if( is_null(array_get($settings, '3.value')) )
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT" name="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT" checked="" type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '3.id') }}">
            @else
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT" name="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_CONTACT" {{ array_get($settings, '3.value.value') === '1' ? 'checked' : '' }} type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '3.id') }}">
            @endif
            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>

        </label>
    </div>
</div>
<hr/>