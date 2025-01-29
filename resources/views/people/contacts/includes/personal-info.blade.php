<h5 class="mb-4">@lang('Basic Info')</h5>

<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group {{$errors->has('type') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('type', __('Type')) }}
            {{ Form::select('type', ['person' => __('Person'), 'organization' => __('Organization')], null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="row" data-showIfOrganization="true" style="@if (!$contact || array_get($contact, 'type') === 'person') display: none; @endif">
    <div class="col-md-6">
        <div class="form-group {{$errors->has('company') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('organization_name', __('Organization Name')) }}
            {{ Form::text('organization_name', null , ['class' => 'form-control', 'placeholder' => __('Organization Name'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<h5 class="my-3" data-showIfOrganization="true" style="@if (!$contact || array_get($contact, 'type') === 'person') display: none; @endif">@lang('Primary Contact Info')</h5>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('first_name'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('first_name') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('first_name') ? 'has-danger':''}}">
            <span class="text-danger" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">*</span> {{ Form::label('first_name', __('First Name')) }}
            {{ Form::text('first_name', null , ['class' => 'form-control', 'placeholder' => __('First Name'), 'required' => (!$contact || array_get($contact, 'type') === 'person'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6">
        @if ($errors->has('first_name'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('last_name') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('last_name') ? 'has-danger':''}}">
            <span class="text-danger" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">*</span>  {{ Form::label('last_name', __('Last Name')) }}
            {{ Form::text('last_name', null , ['class' => 'form-control', 'placeholder' => __('Last Name'), 'required' => (!$contact || array_get($contact, 'type') === 'person'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('middle_name'))
        <span class="help-block">
            <small><strong>{{ $errors->first('middle_name') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('middle_name') ? 'has-danger':''}}">
            {{ Form::label('middle_name', __('Middle Name')) }}
            {{ Form::text('middle_name', null , ['class' => 'form-control', 'placeholder' => __('Middle Name'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-2">
        {{ Form::label('salutation', __('Personal Title')) }}
        {{ Form::select('salutation', [' ' => ' ', 'Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Miss' => 'Miss', 'Ms.' => 'Ms.', 'Mr. and Mrs.' => 'Mr. and Mrs.', 'Dr.' => 'Dr.'], null, ['class' => 'form-control']) }}
    </div>
    <div class="col-md-4">
        <div class="form-group {{$errors->has('preferred_name') ? 'has-danger':''}}">
            {{ Form::label('preferred_name', __('Preferred Name')) }}
            {{ Form::text('preferred_name', null , ['class' => 'form-control', 'placeholder' => __('Preferred Name'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>
    
<div class="row" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">
    <div class="col-md-6">
        <div class="form-group {{$errors->has('company') ? 'has-danger':''}}">
            {{ Form::label('company', __('Organization')) }}
            {{ Form::text('company', null , ['class' => 'form-control', 'placeholder' => __('Organization'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{$errors->has('position') ? 'has-danger':''}}">
            {{ Form::label('position', __('Organization Title')) }}
            {{ Form::text('position', null , ['class' => 'form-control', 'placeholder' => __('Title'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">
    <div class="col-md-6">
        @if ($errors->has('dob'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('dob') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('dob', __('Birthday')) }}
            {{ Form::text('dob', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Birthday'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{$errors->has('gender') ? 'has-danger':''}}">
            {{ Form::label('gender', __('Gender')) }}
            {{ Form::select('gender', ['' => '', 'Male' => __('Male'), 'Female' => __('Female')], null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="row" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">
    <div class="col-md-6">
        <div class="form-group {{$errors->has('marital_status') ? 'has-danger':''}}">
            {{ Form::label('marital_status', __('Marital Status')) }}
            {{ Form::select('marital_status', [
                                            'Single' => __('Single'),
                                            'Married' => __('Married'), 
                                            'Widowed' => __('Widowed'), 
                                            'Separated' => __('Separated'), 
                                            'Divorced' => __('Divorced')
                                        ], null, ['class' => 'form-control']) }}
        </div>
    </div>
    
    <div class="col-md-6" data-anniversaryContainer="true">
        <div class="form-group {{$errors->has('anniversary') ? 'has-danger':''}}">
            {{ Form::label('anniversary', __('Anniversary')) }}
            {{ Form::text('anniversary', null, ['class' => 'form-control datepicker readonly', 'placeholder' => __('Anniversary'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row" data-showIfPerson="true" style="@if (array_get($contact, 'type') === 'organization') display: none; @endif">
    <div class="col-md-6">
        <div class="form-group {{$errors->has('grade') ? 'has-danger':''}}">
            {{ Form::label('grade', __('Grade')) }}
            {{ Form::select('grade', [
                                            '' => '',
                                            'P' => __('P'),
                                            'K' => __('K'), 
                                            '1' => __('1'), 
                                            '2' => __('2'), 
                                            '3' => __('3'),
                                            '4' => __('4'),
                                            '5' => __('5'),
                                            '6' => __('6'),
                                            '7' => __('7'),
                                            '8' => __('8'),
                                            '9' => __('9'),
                                            '10' => __('10'),
                                            '11' => __('11'),
                                            '12' => __('12'),
                                        ], null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

@push('scripts')
<script>
if ($('#marital_status').val() === 'Married') {
    $('[data-anniversaryContainer="true"]').removeClass('d-none');
}
$('#marital_status').change(function () {
    if ($(this).val() === 'Married') {
        $('[data-anniversaryContainer="true"]').removeClass('d-none');
    } else {
        $('[data-anniversaryContainer="true"]').addClass('d-none');
    }
});

$('select[name="type"]').change(function () {
    if ($(this).val() === 'person') {
        $('[data-showIfPerson="true"]').fadeIn();
        $('[data-showIfOrganization="true"]').fadeOut();
        $('#organization_name').prop('required', false);
        $('#first_name').prop('required', true);
        $('#last_name').prop('required', true);
    } else {
        $('[data-showIfPerson="true"]').fadeOut();
        $('[data-showIfOrganization="true"]').fadeIn();
        $('#organization_name').prop('required', true);
        $('#first_name').prop('required', false);
        $('#last_name').prop('required', false);
    }
});
</script>
@endpush