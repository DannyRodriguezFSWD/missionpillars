@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.restore', $contact) !!}
@endsection

@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                @include('people.contacts.includes.contact-profile-short')
                
                <p class="text-muted">
                    This contact has been deleted by 
                    @if (array_get($user, 'id') === auth()->user()->id)
                    you
                    @else
                    {{ array_get($user, 'full_name') }}
                    @endif
                    at {{ array_get($contact, 'deleted_at') }}<br/>
                    Do you want to recover it back?
                </p>
                
                <form method="POST" action="{{ route('contacts.restore', $contact) }}">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-recycle"></i> Recover
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
