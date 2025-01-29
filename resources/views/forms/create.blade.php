@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('forms.create') !!}
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            {{ Form::open(['route' => 'forms.store', 'id' => 'form', 'files' => true]) }}
            @includeIf('forms.includes.form', ['method' => 'Create'])
            {{ Form::close() }}
        </div>
    </div>

@endsection
