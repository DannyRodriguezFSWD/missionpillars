@extends('layouts.app')

@section('content')

@if (session('message'))
<div class="alert alert-success">
    {{ session('message') }}
</div>
@endif

@if ($errors->has('group'))
<div class="alert alert-danger">
    {{ $errors->first('group') }}
</div>
@endif
@if ($errors->has('folder'))
<div class="alert alert-danger">
    {{ $errors->first('folder') }}
</div>
@endif

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('people.contacts.includes.card-header')
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::open(['route'=>'contacts.groupcontact']) }}
                        {{ Form::hidden('cid', Crypt::encrypt($contact->id)) }}
                        {{ Form::hidden('folder', app('request')->input('folder') ? app('request')->input('folder') : $root->id) }}
                        @include('people.contacts.includes.groups-info')
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/.col-->
</div>
@include('people.groups.includes.groups')
@endsection
