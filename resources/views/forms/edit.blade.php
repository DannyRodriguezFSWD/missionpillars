@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('forms.edit',$form) !!}
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            {{ Form::model($form, ['route' => ['forms.update', $form->id], 'id' => 'form', 'files' => true, 'method' => 'PUT']) }}
            {{ Form::hidden('uid', Crypt::encrypt($form->id)) }}
            @includeIf('forms.includes.form', ['method' => 'Edit'])
            {{ Form::close() }}
        </div>
    </div>

@endsection
