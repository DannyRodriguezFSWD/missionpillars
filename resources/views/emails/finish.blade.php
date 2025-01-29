@extends('layouts.app')

@section('content')



<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h4>@lang('Email has been added to queue')</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-right">
                <a class="btn btn-primary" href="{{ route('emails.index') }}">@lang('Finish')</a>
            </div>
        </div>
    </div>

    <div class="card-footer">&nbsp;</div>
</div>




@endsection
