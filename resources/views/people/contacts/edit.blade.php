@extends('layouts.app')
@section('breadcrumbs')
    @isset($editProfile)
    {!! Breadcrumbs::render('contacts.edit-profile', $contact) !!}
    @else
    {!! Breadcrumbs::render('contacts.edit', $contact) !!}
    @endisset
@endsection
@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@include('people.contacts.includes.form')
@include('people.contacts.includes.delete-address-modal')
@include('people.contacts.includes.contact-unsubscribe-modal')

@endsection
