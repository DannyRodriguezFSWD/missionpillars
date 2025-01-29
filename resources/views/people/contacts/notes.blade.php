@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.notes', $contact) !!}
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        @include('people.contacts.includes.card-header')
    </div>
    <div class="card-body">
        @include('notes.index')
    </div>
    
</div>

@endsection
