
<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Basic Info')</p>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <span class="text-danger">*</span> 
        {{ Form::label('name', __('First Name')) }}
        @if ($errors->has('name'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('name') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
            {{ Form::text('name', null , ['class' => 'form-control', 'placeholder' => __('First Name'), 'required'=>true, 'value'=>old('name'), 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-md-6">
        <span class="text-danger">*</span> 
        {{ Form::label('last_name', __('Last Name')) }}
        @if ($errors->has('last_name'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('last_name') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('last_name') ? 'has-danger':''}}">
            {{ Form::text('last_name', null , ['class' => 'form-control', 'placeholder' => __('Last Name'), 'required'=>true, 'value'=>old('last_name'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <span class="text-danger">*</span> 
        {{ Form::label('email', __('Email')) }}
        @if ($errors->has('email'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('email') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('email') ? 'has-danger':''}}">
            {{ Form::email('email', null , ['class' => 'form-control', 'placeholder' => __('user@example.com'), 'required'=>true, 'value'=>old('email'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <span class="text-danger">*</span> 
        {{ Form::label('password', __('Password')) }}
        @if ($errors->has('password'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('password') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('password') ? 'has-danger':''}}">
            {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Password')]) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <span class="text-danger">*</span> 
        {{ Form::label('password_confirmation', __('Repeat Password')) }}
        @if ($errors->has('password'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('password_confirmation') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('password_confirmation') ? 'has-danger':''}}">
            {{ Form::password('password_confirmation', ['class'=>'form-control', 'placeholder'=>__('Repeat Password')]) }}
        </div>
        @if(auth()->user()->can('role-change'))

        <span class="text-danger">*</span> 
        {{ Form::label('role', __('Role')) }}
        @if ($errors->has('role'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('role') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('role') ? 'has-danger':''}}">
            @if(isset($user))
            {{ Form::select('role', $roles, count($user->roles) > 0 ? $user->roles->first()->id : null, ['class'=>'form-control']) }}
            @else
            {{ Form::select('role', $roles, null, ['class'=>'form-control']) }}
            @endif
        </div>
        @endif
    </div>
</div>
@include('people.users.includes.delete-modal')
