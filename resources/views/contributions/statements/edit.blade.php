@extends('layouts.app')

@section('content')

    {{ Form::model($statement, ['route' => ['print-mail.update', array_get($statement, 'id')], 'method' => 'PUT', 'name' => 'print-form']) }}
    {{ Form::hidden('uid', Crypt::encrypt(array_get($statement, 'id'))) }}
    @if(!is_null($contact_id))
    {{ Form::hidden('contact_id', $contact_id) }}
    @endif
    <div class="card">
        <div class="card-header">
            @include('widgets.back')
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <h1>@lang('Print Mail')</h1>
                </div>
                <div class="col-sm-6 text-right pb-2">
                    <div class="" id="floating-buttons">
                        <button id="print" type="submit" class="btn btn-primary">
                            <i class="fa fa-edit"></i> @lang('Save')
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @includeIf('contributions.statements.includes.form')
        <div class="card-footer">&nbsp;</div>
    </div>
    {{ Form::close() }}

@endsection
