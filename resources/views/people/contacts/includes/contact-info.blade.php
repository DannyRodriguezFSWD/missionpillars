<h5 class="mb-4">@lang('Contact Info')</h5>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::checkbox('send_paper_contribution_statement', true, array_get($contact, 'send_paper_contribution_statement', false)) }}
            {{ Form::label('send_paper_contribution_statement', __('Send Paper Contribution Statement')) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('email_1'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('email_1') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('email_1') ? 'has-danger':''}}">
            {{ Form::label('email_1', __('Email 1')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                </div>
                {{ Form::email('email_1', null , ['class' => 'form-control', 'placeholder' => __('email@example.com'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        @if ($errors->has('email_2'))
        <span class="help-block">
            <small><strong>{{ $errors->first('email_2') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('email_2') ? 'has-danger':''}}">
            {{ Form::label('email_2', __('Email 2')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                </div>
                {{ Form::text('email_2', null , ['class' => 'form-control', 'placeholder' => __('email@example.com'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('cell_phone'))
        <span class="help-block">
            <small><strong>{{ $errors->first('cell_phone') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('cell_phone') ? 'has-danger':''}}">
            {{ Form::label('cell_phone', __('Mobile')) }} 
            @if (array_get($contact, 'unsubscribed_from_phones')) 
            <span class="text-warning cursor-pointer" data-toggle="modal" data-target="#contact-unsubscribe-modal">(@lang('Unsubscribed from'): <span id="unsubscribed-phones">{{ array_get($contact, 'unsubscribed_from_phones') }}</span> <i class="fa fa-edit"></i>)</span>
            @endif
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">+1</span>
                </div>
                {{ Form::text('cell_phone', null , ['class' => 'form-control', 'placeholder' => __('Mobile Number'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        @if ($errors->has('home_phone'))
        <span class="help-block">
            <small><strong>{{ $errors->first('home_phone') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('home_phone') ? 'has-danger':''}}">
            {{ Form::label('home_phone', __('Home Phone')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">+1</span>
                </div>
                {{ Form::text('home_phone', null , ['class' => 'form-control', 'placeholder' => __('Home Number'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('work_phone'))
        <span class="help-block">
            <small><strong>{{ $errors->first('work_phone') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('work_phone') ? 'has-danger':''}}">
            {{ Form::label('work_phone', __('Work Phone')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text">+1</span>
                </div>
                {{ Form::text('work_phone', null , ['class' => 'form-control', 'placeholder' => __('Work Number'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        @if ($errors->has('other_phone'))
        <span class="help-block">
            <small><strong>{{ $errors->first('other_phone') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('other_phone') ? 'has-danger':''}}">
            {{ Form::label('work_phone', __('Other Phone')) }}
            {{ Form::text('other_phone', null , ['class' => 'form-control', 'placeholder' => __('Other Number'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        @if ($errors->has('website'))
        <span class="help-block">
            <small><strong>{{ $errors->first('website') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('website') ? 'has-danger':''}}">
            {{ Form::label('website', __('Website')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-link"></i></span>
                </div>
                {{ Form::text('website', null , ['class' => 'form-control', 'placeholder' => __('www.example.com'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        @if ($errors->has('facebook'))
        <span class="help-block">
            <small><strong>{{ $errors->first('facebook') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('facebook') ? 'has-danger':''}}">
            {{ Form::label('facebook', __('Facebook')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-facebook"></i></span>
                </div>
                {{ Form::text('facebook', null , ['class' => 'form-control', 'placeholder' => __('Facebook'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        @if ($errors->has('twitter'))
        <span class="help-block">
            <small><strong>{{ $errors->first('twitter') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('twitter') ? 'has-danger':''}}">
            {{ Form::label('twitter', __('Twitter')) }}
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-twitter"></i></span>
                </div>
                {{ Form::text('twitter', null , ['class' => 'form-control', 'placeholder' => __('Twitter'), 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>
