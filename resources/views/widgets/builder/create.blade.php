@extends('layouts.app')

@section('content')

<div class="card">
    {{ Form::open(['route' => 'widgets.store']) }}
    <div class="card-header">
        <a href="javascript:history.back()">
            <span class="fa fa-chevron-left"></span> @lang('Back')
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 text-right">
                <div class="btn-group btn-group" role="group" aria-label="...">
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-edit"></i> 
                            @lang('Save')
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('name', __('Chart title')) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Untitled Chart', 'required' => true, 'autocomplete' => 'off']) }}
        </div>
    </div>
    <hr/>
    
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('measurement', __('Measurement')) }}<br>
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-primary active">
                            {{ Form::radio('measurement', '#', true) }}
                            @lang('#')
                        </label>
                        <label class="btn btn-primary">
                            {{ Form::radio('measurement', '$') }} @lang('$')
                        </label>
                        <label class="btn btn-primary">
                            {{ Form::radio('measurement', '%') }} @lang('%')
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('calculate', __('Calculate Chart By')) }}
                    {{ Form::select('calculate', ['total' => __('Total'), 'comparison' => __('Comparison')], null, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('what', __('What total would you like to see?')) }}
                    {{ Form::select('what', ['attendance' => 'Attendance', 'contacts' => 'Contacts', 'giving' => 'Giving'], null, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </div>

    @include('widgets.builder.fragments.total-option')
    @include('widgets.builder.fragments.filters')

    <div class="card-footer"></div>
    {{ Form::close() }}
</div>

@endsection
