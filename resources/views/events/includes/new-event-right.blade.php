
<div class="row">
    <div class="col-sm-12">
        <h5>Custom Forms</h5>
    </div>
</div>

<div class="row">
    <div class="col-sm-12" id="form-setting">
        <div class="form-group">
            {{ Form::label('form_id', __('Optional registration form')) }}:
            <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip" data-placement="right" title="The selected form will be displayed after a person registers for an event."></i>
            @if ($errors->has('form_id'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('form_id') }}</strong></small>
            </span>
            @endif
            <p>*Note: The event registration will already capture the user's name and email.  Use an additional form if you want to ask additional questions such as allergies, etc. </p>
            {{ Form::select('form_id', $dropDownForms, isset($event) && !is_null( $event->linkedForm ) ? array_get($event->linkedForm, 'id') : null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group" id="incomplete-form-respondents">
            <div class="d-flex">
                <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                    @if(isset($event) && array_get($event, 'form_must_be_filled', 0) > 0)
                        <input name="form_must_be_filled" class="c-switch-input" value="{{ array_get($event, 'form_must_be_filled', 0) }}" checked="true" id="allow-incomplete-form-respondents" type="checkbox">
                    @else
                        <input name="form_must_be_filled" class="c-switch-input" value="1" id="allow-incomplete-form-respondents" type="checkbox">
                    @endif
                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                </label>
                <label for="form_must_be_filled">@lang('Form must be filled to check-in')</label>
            </div>
        </div>


    </div>
</div>

<div class="row">

    <div class="col-sm-12" id="event-forms">
        <ul>
            @foreach($forms as $form)
            <li>
                <input id="form-{{ $form->id }}" name="forms[]" type="checkbox" class="form" value="{{ $form->id }}"/>
                <span class="fa fa-list-alt"></span> {{ $form->name }}
            </li>
            @endforeach
        </ul>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    (function () {

        $('select[name=form_id]').on('change', function (e) {
            var value = $(this).val();
            if (value > 1) {
                $('#incomplete-form-respondents').fadeIn();
            } else {
                $('#incomplete-form-respondents').fadeOut();
            }
        });

        $('select[name=form_id]').trigger('change');

    })();
</script>
@endpush
