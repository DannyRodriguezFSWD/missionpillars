<div class="form-group row">    
    {{ Form::label('PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN', __('Send email to receiver when a new pledge is made'), ['class' => 'col-md-10 col-form-label']) }}
    <div class="col-sm-2 text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            @if( is_null(array_get($settings, '6.value')) )
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN" name="PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN" checked="" type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '6.id') }}">
            @else
            <input id="PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN" name="PLEDGE_EMAIL_REMINDER_SWITCH_NEW_PLEDGE_ADMIN" {{ array_get($settings, '6.value.value') === '1' ? 'checked' : '' }} type="checkbox" class="c-switch-input pledge-notification-switch" value="{{ array_get($settings, '6.id') }}">
            @endif
            <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>

        </label>
    </div>
</div>
<hr/>