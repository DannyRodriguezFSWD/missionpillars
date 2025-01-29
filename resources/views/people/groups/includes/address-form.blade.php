<div class="row">
    <div class="col-md-12">
        @if ($errors->has('mailing_address_1'))
        <span class="help-block">
            <small><strong>{{ $errors->first('mailing_address_1') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('mailing_address_1') ? 'has-danger':''}}">
            {{ Form::label('mailing_address_1', __('Mailing Address 1')) }}
            {{ Form::text('mailing_address_1', null , ['class' => 'form-control', 'placeholder' => __('Mailing Adddress 1'), 'required'=>true, 'value'=>old('mailing_address_1')]) }}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        @if ($errors->has('mailing_address_2'))
        <span class="help-block">
            <small><strong>{{ $errors->first('mailing_address_2') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('mailing_address_2') ? 'has-danger':''}}">
            {{ Form::label('mailing_address_2', __('Mailing Adddress 2')) }}
            {{ Form::text('mailing_address_2', null , ['class' => 'form-control', 'placeholder' => __('Mailing Adddress 2'), 'value'=>old('mailing_address_2')]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('p_o_box'))
        <span class="help-block">
            <small><strong>{{ $errors->first('p_o_box') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('p_o_box', __('PO Box')) }}
            {{ Form::text('p_o_box', null, ['class' => 'form-control', 'placeholder' => __('PO Box')]) }}
        </div>
    </div>
    <div class="col-md-6">
        @if ($errors->has('city'))
        <span class="help-block">
            <small><strong>{{ $errors->first('city') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('city', __('City')) }}
            {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('City')]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('region'))
        <span class="help-block">
            <small><strong>{{ $errors->first('region') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('region', __('Region')) }}
            {{ Form::text('region', null, ['class' => 'form-control', 'placeholder' => __('Region')]) }}
        </div>
    </div>
    <div class="col-md-6">
        @if ($errors->has('p_o_box'))
        <span class="help-block">
            <small><strong>{{ $errors->first('country') }}</strong></small>
        </span>
        @endif

        <div class="form-group {{$errors->has('country') ? 'has-danger':''}}">
            {{ Form::label('country_id', __('Country')) }}
            {{ Form::select('country_id', $countries, null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('postal_code'))
        <span class="help-block">
            <small><strong>{{ $errors->first('postal_code') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('postal_code', __('Postal Code')) }}
            {{ Form::text('postal_code', null, ['class' => 'form-control', 'placeholder' => __('Postal Code')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('is_residence', __('Is Residence')) }}
            <div>
                <label class="c-switch c-switch-label  c-switch-primary">
                    @if(isset($address))
                        <input type="checkbox" name="is_residence" class="c-switch-input" value="1" {{ $address->is_residence ? 'checked' : '' }}>
                    @else
                        <input type="checkbox" name="is_residence" class="c-switch-input" value="1">
                    @endif
                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                </label>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('is_mailing', __('Is Primary')) }}
            <i class="fa fa-question-circle text-info cursor-pointer" data-toggle="tooltip" title="Primary address will be used for mailing in our communications module."></i>
            <div>
                <label class="c-switch c-switch-label  c-switch-primary">
                    @if(isset($address))
                        <input type="checkbox" name="is_mailing" class="c-switch-input" value="1" {{ $address->is_mailing ? 'checked' : '' }}>
                    @else
                        <input type="checkbox" name="is_mailing" class="c-switch-input" value="1">
                    @endif
                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                </label>
            </div>
        </div>
    </div>
</div>