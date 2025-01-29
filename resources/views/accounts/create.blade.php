@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'accounts.store']) }}
                <div class="row">
                    <div class="col-md-10">
                        <h1 class="">@lang('Create New Account')</h1>
                    </div>
                    <div class="col-md-2 text-right">
                        <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                            <i class="icons icon-note"></i> @lang('Save')
                        </button>
                    </div>
                </div>
                
                
                <br>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('name', __('Name')) }}
                            @if ($errors->has('name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('name') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'Off', 'required' => true]) }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('group') ? 'has-danger':''}}">
                            <span class="text-danger">*</span> {{ Form::label('group', __('Group')) }}
                            @if ($errors->has('name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('group') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::select('group', $groups, null, ['placeholder' => 'Select Group', 'class'=>'form-control']) }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group {{ $errors->has('group') ? 'has-danger':'' }}">
                            <span class="text-danger">*</span> {{ Form::label('number', __('Number')) }}
                            @if ($errors->has('number'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('number') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('number', '', ['class'=>'form-control']) }}
                            
                        </div>
                    </div>
                </div>
                
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@include('accounts.includes.create-group-modal')

@push('scripts')
<script type="text/javascript">
    (function(){
        
        $document.on('c')
        
    })();
</script>
@endpush

@endsection
