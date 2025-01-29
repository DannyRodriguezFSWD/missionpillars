@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-12 text-center">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> Back
                </a>
                {{ array_get($form, 'name') }}
            </div>
        </div>
    </div>  
    @include('entries.includes.data')
</div>
@include('forms.includes.delete-entry-modal')
@include('forms.includes.select-contact-modal')

@endsection
        