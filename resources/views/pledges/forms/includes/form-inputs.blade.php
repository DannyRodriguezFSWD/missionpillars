<div class="row">
    <div class="col-sm-12">
        <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('name', __('Form Name')) }}
            @if ($errors->has('name'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('name') }}</strong></small>
            </span>
            @endif

            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'off', 'required' => true]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group {{$errors->has('description') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('name', __('Description')) }}
            @if ($errors->has('description'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('description') }}</strong></small>
            </span>
            @endif

            <div id="tinyTextarea" class="tinyTextarea">{!! array_get($form, 'description') !!}</div>
            {{ Form::hidden('content') }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('campaign_id', __('Fundraiser')) }}
            {{ Form::select('campaign_id', $campaigns, null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group {{$errors->has('purpose_id') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('purpose_id', __('Purpose')) }}
            @if ($errors->has('purpose_id'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
            </span>
            @endif

            {{ Form::select('purpose_id', $charts, null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('form_id', __('Custom Form')) }}
            {{ Form::select('form_id', $forms, null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>