@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('people.contacts.includes.card-header')
    </div>
    @include('entries.includes.data')
</div>

@endsection
