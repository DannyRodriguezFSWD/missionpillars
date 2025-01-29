@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <h2>@lang('Group Signup')</h2>
            <h5>{{ $tenant->organization }}</h5>
            
            @foreach($groups as $group)
            
            {{ Form::open(['route' => 'redirect.store']) }}
            {{ Form::hidden('start_url', request()->fullUrl()) }}
            {{ Form::hidden('next_url', route('join.show', ['id' => $group->uuid])) }}
            
            <div class="card">
                <div class="card-body clearfix">
                    <button type="submit" class="btn btn-link btn-block">
                    <i class="icon icon-people bg-primary p-4 font-2xl mr-3 float-left"></i>
                    <div class="h5 text-primary mb-0 pt-3 text-left">{{ $group->name }}</div>
                    <div class="text-muted text-uppercase font-weight-bold font-xs text-left">@lang('Join Now!')</div>
                    </button>
                </div>
            </div>
            
            {{ Form::close() }}
            
            @endforeach
        </div>
    </div>
</div>

@endsection
