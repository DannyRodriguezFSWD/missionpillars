@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>@lang('Event Sign-Up')</h2>
            <h5>{{ $tenant->organization }}</h5>
            
            @foreach($events as $event)
            <a href="{{ route('events.share', ['id' => $event->uuid]) }}" style="text-decoration: none;">
                <div class="card mx-8">
                    <div class="card-body p-0 clearfix">
                        <i class="icon icon-people bg-primary p-4 font-2xl mr-3 float-left"></i>
                        <div class="h5 text-primary mb-0 pt-3">{{ $event->name }}</div>
                        <div class="text-muted text-uppercase font-weight-bold font-xs">@lang('Join Now!')</div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

@endsection
