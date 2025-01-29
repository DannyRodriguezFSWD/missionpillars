@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.settings',$event) !!}
@endsection
@section('content')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush
@include('events.includes.functions')
        <div class="row">
            <div class="col-md-12">
                {{ Form::model($event, ['route' => ['events.update', $split->id], 'method' => 'PUT', 'id' => 'form', 'files' => true]) }}
                {{ Form::hidden('uid', Crypt::encrypt($split->id)) }}
                <div class="row">
                    <div class="d-md-none col-md-11 text-right pb-2 position-sticky" style="pointer-events: none; top: 60px; z-index: 2">
                        <div class="" id="floatings-buttons">
                            <button type="submit" class="btn btn-primary" style="pointer-events: auto; ">
                                <i class="icons icon-note"></i>
                                @lang('Save')
                            </button>
                        </div>
                    </div>
                    <div class="d-md-none col-md-1"></div>
                    <div class="col-md-1"></div>
                    @include('events.includes.event-settings-menu')
                    <div class="col-md-8 mt-2 mt-md-0">
                        @include('events.includes.fragments.event')
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
@include('events.includes.fragments.ticket-option-modal')
@include('events.includes.fragments.scripts')
@endsection
