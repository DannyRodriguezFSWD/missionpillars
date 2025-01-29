@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::model($email, ['route' => ['emails.update', array_get($email, 'id')], 'id' => 'form', 'files' => true, 'method' => 'PUT']) }}
        {{ Form::hidden('uid', Crypt::encrypt(array_get($email, 'id'))) }}
        {{ Form::hidden('action', 'preview') }}
        
        @include('emails.includes.form')
        
        {{ Form::close() }}

    </div>

    <div class="card-footer">&nbsp;</div>
</div>
@include('people.contacts.includes.emails.email-empty-modal')
@include('includes.overlay')

@endsection
