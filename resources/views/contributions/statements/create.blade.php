@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        {{ Form::open(['route' => 'print-mail.store', 'name' => 'print-form']) }}
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>@lang('Print Mail')</h1>
                    </div>
                    <div class="col-sm-4 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <button id="print" type="submit" class="btn btn-primary">
                                <i class="fa fa-print"></i> @lang('Save')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @include('contributions.statements.includes.form')
            <div class="card-footer">&nbsp;</div>
        </div>
        {{ Form::close() }}
    </div>
</div>

@endsection
