@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.directory') !!}
@endsection

@section('content')

@include('people.contacts.includes.contacts-directory')

@endsection