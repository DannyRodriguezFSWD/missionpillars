@extends('layouts.public')

@section('content')

<div id="overlay" class="app-loader">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
    </div>
</div>

<div class="row bg-info">
    <div class="col-5">
        <span id="eventNameHeader" class="text-white"></span>
        <span id="groupNameHeader" class="text-white ml-1"></span>
    </div>
    <div class="col-2">
        <h2 class="text-center mt-1">@lang('Checkin')</h2>
    </div>
    <div class="col-5">
        <button class="btn btn-lg btn-info pull-right" data-tooltip="tooltip" title="Settings" data-toggle="modal" data-target="#checkinSettingsModal">
            <i class="fa fa-cog"></i>
        </button>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6 offset-md-3">
        <center>
            <button id="checkinAlert" class="btn btn-lg btn-primary btn-block py-5" data-toggle="modal" data-target="#checkinSettingsModal">
                <span style="font-size: 30px;"><i class="fa fa-cogs mr-3"></i> Click here to setup</span>
            </button>
        </center>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <input type="text" id="searchCheckinContacts" class="form-control input-lg mb-3 d-none" placeholder="Search by name or email..." autocomplete="off" />

        <button id="printTags" class="btn btn-primary mb-3 d-none" onclick="showPrintTags()">
            <i class="fa fa-print"></i> Print Tags
        </button>
        
        <div class="alert alert-info h5 d-none" id="addContactAlert">
            @lang('Cannot find the contact?') 
            <a href="#" onclick="$('#contactFormContainer').fadeToggle()">
                <i class="fa fa-plus-circle"></i> @lang('Click here')
            </a>
            @lang('to add them').
        </div>
        
        <div id="contactFormContainer" class="mt-3"  style="display: none;">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                @lang('Contact\'s Name')
                                <i class="fa fa-times pull-right cursor-pointer" onclick="$('#contactFormContainer').fadeToggle()"></i>
                            </h5>
                            <div id="people-search-with-create">
                                <people-search-with-create
                                    :add_to_group="true"
                                    :on_save_contact="true"
                                    :hide_title="true"
                                    :show_group_search="true"
                                    :show_family_search="true"
                                    :groups="{{ $groups }}"
                                ></people-search-with-create>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div data-peopleList="true" style="height: calc(100vh - 230px); overflow-y: auto;"></div>
    </div>
</div>

@include ('checkin.includes.settings.modal')
@include ('checkin.includes.print-modal')
@include ('checkin.includes.print-sticker')
@include ('checkin.includes.scripts')

@endsection
