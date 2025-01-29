@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('people.contacts.includes.card-header')
    </div>
    <div class="card-body">
        @include('people.contacts.includes.background-info')
    </div>
    
</div>

@endsection
