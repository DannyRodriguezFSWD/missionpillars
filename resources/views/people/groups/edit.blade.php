@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('groups.edit', $group) !!}
@endsection

@section('content')

{{ Form::model($group, ['route' => ['groups.update', $group->id], 'method' => 'PUT', 'id' => 'form', 'files' => true]) }}
{{ Form::hidden('uid',  Crypt::encrypt($group->id), ['id' => 'uid']) }}
<div class="row">
    <div class="col-sm-8 offset-md-2 text-right pb-2 position-sticky" style="top: 60px; z-index: 2; pointer-events: none">
        <button id="btn-submit-contact" type="submit" class="btn btn-primary" style="pointer-events: auto">
            <i class="icons icon-note"></i>
            @lang('Save')
        </button>
    </div>
    <div class="col-md-8 offset-md-2">
        @include('people.groups.includes.form')
    </div>
</div>
{{ Form::close() }}

@include('people.groups.includes.delete-address-modal')
@include('people.groups.includes.share-group-modal')
@include('people.groups.includes.groups')
@endsection
