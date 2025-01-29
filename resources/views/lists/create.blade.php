@extends('layouts.app')

@section('content')

@include('lists.includes.functions')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => 'lists.store']) }}
        

        @include('lists.includes.form')

        {{ Form::close() }}
    </div>
</div>

@endsection
