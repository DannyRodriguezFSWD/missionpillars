@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mx-8">
                {{Form::open(['route' => ['tenants.update', array_get(auth()->user(), 'tenant.id', 0)], 'method' => 'PUT'])}}
                {{ Form::hidden('uid', Crypt::encrypt(array_get(auth()->user(), 'tenant.id', 0))) }}
                <div class="card-body">
                    @include('layouts.pricing')
                </div>
                <div class="card-body p-4">
                    <button type="submit" class="btn btn-block btn-primary btn-lg">@lang('Upgrade Now')</button>
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
</div>

@endsection
