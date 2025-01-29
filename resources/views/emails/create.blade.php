@extends('layouts.app')

@section('content')



<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['emails.store'], 'id' => 'form', 'files' => true]) }}
        {{ Form::hidden('action', 'preview') }}
        
        @include('emails.includes.form')
        
        {{ Form::close() }}

    </div>

    <div class="card-footer">&nbsp;</div>
</div>
@include('people.contacts.includes.emails.email-empty-modal')
@include('includes.overlay')

@endsection
