<div class="form-group row">    
    {{ Form::label('PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN', __('Send email to receiver when a payment is made toward a pledge'), ['class' => 'col-md-10 col-form-label']) }}
    <div class="col-sm-2 text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            @if( is_null(array_get($settings, '4.value')) )
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN" name="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN" checked="" type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '4.id') }}">
            @else
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN" name="PLEDGE_EMAIL_REMINDER_SWITCH_PAYMENT_ADMIN" {{ array_get($settings, '4.value.value') === '1' ? 'checked' : '' }} type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '4.id') }}">
            @endif
            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>

        </label>
    </div>
</div>
<hr/>