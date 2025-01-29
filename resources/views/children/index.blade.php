@extends('layouts.children-checkin')

@section('content')

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
            <!-- <h3>{{ date('l jS \of F Y h:i A') }}</h3> -->
            <h3>@lang('Child Check In')</h3>
            <h3>@lang('Step one: Search for yourself')</h3>
        </div>
    </div>

    <div class="search-box">
        {{ Form::open(['route' => ['child-checkin.parent.search'], 'method' => 'GET']) }}
        <div class="form-group-lg">
            <div class="input-group input-group-lg">
                {{ Form::text('keyword', null, ['class' => 'form-control', 'placeholder' => 'Type Your First Name, Last Name, Email or Cell Phone Number', 'autocomplete' => 'off', 'required' => true]) }}
                <span class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> @lang('Search')</button>
                </span>
            </div>
        </div>
        {{ Form::close() }}
    </div>

</div>
<!--
<div style="width: 100%; background: rgba(0, 0, 0, 0.2); color: #fff; position: fixed; bottom: 0; left: 0;" class="p-4">
    <h1 class="text-center" style="color: #fff; text-shadow: 0px 0px 10px rgba(0, 0, 0, 1);">@lang('Type In Your First Name, Lastname, Email or Phone Number')</h1>
</div>
-->
@endsection
