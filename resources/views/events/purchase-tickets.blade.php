@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            
            <div class="card mx-2">
                <div class="card-body">
                    {{ Form::open(['route' => ['events.purchase.tickets', array_get($split, 'id')], 'method' => 'POST']) }}
                    {{ Form::hidden('total') }}
                    {{ Form::hidden('contact_id', array_get($contact, 'id')) }}
                    {{ Form::hidden('register_id', array_get($register, 'id')) }}
                    {{ Form::hidden('ticket_id', $ticket) }}
                    @include('events.includes.share.tickets')
                    
                    {{ Form::close() }}
                </div>
                
                @include('shared.sessions.start-over-button')
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    /*
    .delete-ticket{
        display: none;
    }
    */
</style>
@endpush

@endsection
