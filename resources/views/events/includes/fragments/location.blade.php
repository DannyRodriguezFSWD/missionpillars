<div class="row">
    <div class="col-sm-12">
        @if ($errors->has('mailing_address_1'))
        <span class="help-block">
            <small><strong>{{ $errors->first('mailing_address_1') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('mailing_address_1') ? 'has-danger':''}}">
            {{ Form::label('mailing_address_1', __('Address')) }}
            {{ Form::text('mailing_address_1', array_get($event, 'addressInstance.0.mailing_address_1') , ['class' => 'form-control', 'placeholder' => __('Adddress'), 'value'=>old('mailing_address_1'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4">
        @if ($errors->has('city'))
        <span class="help-block">
            <small><strong>{{ $errors->first('city') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('city', __('City')) }}
            {{ Form::text('city', array_get($event, 'addressInstance.0.city'), ['class' => 'form-control', 'placeholder' => __('City'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        @if ($errors->has('region'))
        <span class="help-block">
            <small><strong>{{ $errors->first('region') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('region', __('State/Region')) }}
            {{ Form::text('region', array_get($event, 'addressInstance.0.region'), ['class' => 'form-control', 'placeholder' => __('Region'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        @if ($errors->has('country'))
        <span class="help-block">
            <small><strong>{{ $errors->first('country') }}</strong></small>
        </span>
        @endif

        <div class="form-group {{$errors->has('country') ? 'has-danger':''}}">
            {{ Form::label('country_id', __('Country')) }}
            {{ Form::select('country_id', $countries, array_get($event, 'addressInstance.0.country_id'), ['class' => 'form-control']) }}
        </div>
    </div>
</div>
