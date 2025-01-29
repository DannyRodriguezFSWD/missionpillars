
<div class="form-group row">
    {{ Form::label('every', __('Send reminder every'), ['class' => 'col-md-3 col-form-label']) }}
    <div class="col-md-2">
        {{ Form::number('PLEDGE_EMAIL_REMINDER_TEXT_EVERY', array_get($settings, '1.value.value', 5), ['class' => 'form-control text-center email_reminder_input', 'min' => 1]) }}
    </div>
    {{ Form::label('before', __('days'), ['class' => 'col-md-1 col-form-label']) }}
    {{ Form::label('starting', __('starting'), ['class' => 'col-md-1 col-form-label text-right']) }}
    <div class="col-md-2">
        {{ Form::number('PLEDGE_EMAIL_REMINDER_TEXT_STARTING', array_get($settings, '2.value.value', 20), ['class' => 'form-control text-center email_reminder_input', 'min' => 1]) }}
    </div>
    {{ Form::label('date', __('days before the promised date'), ['class' => 'col-md-3 col-form-label']) }}
</div>

@push('styles')
<style>
    .form-control:disabled, .form-control[readonly] {
        background-color: #c2cfd6;
        opacity: 1;
    }
</style>
@endpush
