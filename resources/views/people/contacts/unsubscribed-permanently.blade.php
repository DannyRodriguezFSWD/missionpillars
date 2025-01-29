@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    You have permanently unsubscribed from all email communications from {{ array_get($contact, 'tenant.organization') }}.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
