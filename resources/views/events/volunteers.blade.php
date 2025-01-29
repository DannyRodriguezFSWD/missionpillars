@extends('layouts.app')
@section('content')

@section('content')
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush
@include('events.includes.functions')

<div class="card" ng-app="CheckInApplication" ng-cloak="">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('events.index') }}">@lang('Events')</a>
        </li>
        <li class="breadcrumb-item active">{{ $event->name }}</li>
    </ol>
    <div class="card-body">
        <div class="row">
            @include('events.includes.event-settings-menu')
            <div class="col-sm-9 vertical-menu-bar">
                <ul class="nav nav-tabs" role="tablist">
                    
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#contacts" role="tab" aria-controls="home" aria-expanded="true">
                            <i class="fa fa-user"></i> @lang('Contacts')
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tags" role="tab" aria-controls="profile" aria-expanded="false">
                            <i class="fa fa-tags"></i> @lang('Tags')
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#forms" role="tab" aria-controls="profile" aria-expanded="false">
                            <i class="fa fa-list-alt"></i> @lang('Forms')
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#checkedin" role="tab" aria-controls="profile" aria-expanded="false">
                            <i class="fa fa-check-circle"></i> @lang('Checked In')
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content" ng-controller="CheckIn">
                    
                    <div class="tab-pane active" id="contacts" role="tabpanel" aria-expanded="true">
                        <div class="form-group">
                            {{ Form::label('contact', __('Name')) }}
                            {{ Form::text('contact', null, ['id' => 'autocomplete', 'class' => 'form-control']) }}
                        </div>
                        {{ Form::open(['route' =>['events.checkincontacts', $event->id, 'action=volunteers']]) }}
                        {{ Form::hidden('autocomplete', route('contacts.autocomplete')) }}
                        {{ Form::hidden('url', route('tags.get') ) }}
                        <button id="btn-submit-contact" type="submit" class="btn btn-primary" style="position: fixed; top: 95px; right: 36px; z-index: 99;"><i class="icons icon-note"></i> Save</button>
                        <table class="table table-striped">
                            <tbody>
                                <tr ng-repeat="item in items">
                                    <td>
                                        <span class="icon icon-user"></span>
                                        @{{ item.value }}
                                        <input name="contacts[]" type="hidden" value="@{{ item.id }}"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        {{ Form::close() }}
                    </div>
                    
                    <div class="tab-pane" id="tags" role="tabpanel" aria-expanded="false">
                        <ol class="tree">
                            <?php printFoldersTree($tree); ?>
                        </ol>
                    </div>
                    
                    <div class="tab-pane" id="forms" role="tabpanel" aria-expanded="false">
                        @foreach( $event->forms as $form )
                        <ul>
                            <li>
                                <input id="form-{{ $form->id }}" class="form" type="checkbox" name="forms[]" value="{{ $form->id }}"/>
                                <span class="fa fa-list-alt"></span> {{ $form->name }}
                            </li>
                        </ul>
                        @endforeach
                    </div>
                    
                    <div class="tab-pane" id="checkedin" role="tabpanel" aria-expanded="false">
                        <table class="table table-striped">
                            <tbody>
                            @foreach( $event->checkInVolunteers as $volunteer )
                                <tr>
                                    <td>
                                        <span class="icon icon-people"></span> 
                                        {{ $volunteer->first_name }}
                                        {{ $volunteer->last_name }}
                                        ({{ $volunteer->email_1 }})
                                    </td>
                                    <td class="text-right">
                                        {{ Form::open(['route' => ['events.uncheck', $event->id, $volunteer->id, 'action=volunteers'], 'method' => 'DELETE', 'onsubmit' => 'return confirm('.__("'Are you sure you want to remove this contact?'").')']) }}
                                        {{ Form::hidden('uid', Crypt::encrypt($event->id)) }}
                                        <button type="submit" class="btn btn-link text-danger">
                                            <span class="icon icon-close"></span>
                                        </button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<div id="overlay">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
        <!-- <p>Wait a moment please</p> -->
    </div>
</div>

@push('scripts')
<script type="text/javascript" src="{{ asset('js/angular.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/checkin/checkin.run.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/forms/jquery.autocomplete.min.js') }}"></script>
@endpush

@endsection
