@extends('layouts.children-checkin')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <a href="{{ route('child-checkin.index') }}" class="btn btn-success btn-lg btn-block">
            <span class="icon icon-reload"></span> @lang('Start Over')
        </a>
    </div>
</div>

<div class="container children">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-12 titles">
            <h1>{{ $tenant->organization }}</h1>
            <h3>@lang('Step two: Select Yourself')</h3>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-4">
            <a href="{{ route('child-checkin.create', ['action' => 'parent']) }}" style="text-decoration: none;">
                <div class="card card-inverse card-success">
                    <div class="card-body">
                        <div class="h1 text-muted text-right mb-4">
                            <i class="icon-user-follow"></i>
                        </div>
                        <div class="h4 mb-0">@lang('Add Myself')</div>
                        <small class="text-muted text-uppercase font-weight-bold">@lang("To ") {{ $tenant->organization }}</small>
                    </div>
                </div>
            </a>
        </div>
        @foreach($found as $f)
        <div class="col-sm-4">
            <a href="{{ route('child-checkin.show', ['id' => $f->id]) }}" style="text-decoration: none;">
                <div class="card card-inverse card-primary">
                    <div class="card-body">
                        <div class="h1 text-muted text-right mb-4">
                            <i class="icon-user"></i>
                        </div>
                        <div class="h4 mb-0">{{ $f->first_name }} {{ $f->last_name }}</div>
                        <small class="text-muted text-uppercase font-weight-bold">{{ $tenant->organization }}</small>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

</div>
<!--
<div style="width: 100%; background: rgba(0, 0, 0, 0.2); color: #fff; position: fixed; bottom: 0; left: 0;" class="p-4">
    <h1 class="text-center" style="color: #fff; text-shadow: 0px 0px 10px rgba(0, 0, 0, 1);">@lang('Type In Your First Name, Lastname, Email or Phone Number')</h1>
</div>
-->
@endsection
