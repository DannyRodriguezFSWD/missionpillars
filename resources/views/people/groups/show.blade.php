@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('groups.show', $group) !!}
@endsection

@section('content')

<ul class="nav nav-tabs border-0 mb-3 h4" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $showMembers ? '' : 'active' }} " id="group-info-tab" data-toggle="tab" href="#group-info" role="tab" aria-controls="home" aria-selected="true">
            <i class="fa fa-info-circle"></i> @lang('Group Info')
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ $showMembers ? 'active' : '' }}" id="group-members-tab" data-toggle="tab" href="#group-members" role="tab" aria-controls="profile" aria-selected="false">
            <i class="fa fa-users"></i> @lang('Group Members')
        </a>
    </li>
    
    @if (false)
    <li class="nav-item">
        <a class="nav-link" id="group-events-tab" data-toggle="tab" href="#group-events" role="tab" aria-controls="events" aria-selected="false">
            <i class="fa fa-calendar"></i> @lang('Events')
        </a>
    </li>
    @endif
    
    @if (auth()->user()->can('group-update'))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">@lang('Group Actions')</a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('groups.edit', $group) }}">
                <i class="fa fa-edit"></i>&nbsp;@lang('Edit Group')
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#members-edit-modal" onclick="manageMembers();">
                <i class="fa fa-users"></i>&nbsp;@lang('Manage Members')
            </a>
            @if(auth()->user()->can('events-view') && auth()->user()->can('group-view'))
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('events.create').'?group='.array_get($group, 'id') }}">
                <i class="fa fa-calendar"></i>&nbsp;@lang('New Event')
            </a>
            <a class="dropdown-item" href="{{ route('checkin.index', array_get($group, 'uuid')) }}" target="_blank">
                <i class="fa fa-check-square-o"></i>&nbsp;@lang('Checkin Members')
            </a>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#attendance-report-options-modal">
                <i class="fa fa-file-excel-o"></i>&nbsp;@lang('Download Attendance Report')
            </a>
            @endif
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('groups.excel', $group) }}">
                <i class="fa fa-file-excel-o"></i>&nbsp;@lang('Export Members')
            </a>
            <a class="dropdown-item" href="{{ route('groups.pdf-picture-directory', $group) }}">
                <i class="fa fa-file-pdf-o"></i>&nbsp;@lang('Download Picture Directory')
            </a>
            @if (auth()->user()->can('communications-menu'))
            <a class="dropdown-item" href="{{ route('groups.email', $group) }}">
                <i class="fa fa-envelope"></i>&nbsp;@lang('Send Email')
            </a>
            @if (env('APP_MASS_MESSAGE_AVAILABLE'))
            <a class="dropdown-item" href="{{ route('groups.sms', $group) }}">
                <i class="fa fa-comment"></i>&nbsp;@lang('Send SMS')
            </a>
            @endif
            @endif
            @if (auth()->user()->can('group-delete'))
            <div class="dropdown-divider"></div>
            @include('people.groups.includes.delete-group')
            @endif
        </div>
    </li>
    @endif
</ul>

<div class="tab-content">
    <div class="tab-pane fade {{ $showMembers ? '' : 'show active' }}" id="group-info" role="tabpanel" aria-labelledby="group-info-tab">
        <div class="row animated fadeInRight">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body p-0">
                        <img src="{{ $group->cover_image_src }}" class="card-img-top" alt="{{ $group->name }}" />

                        <div class="p-4">
                            <h4><strong>{{ $group->name }}</strong></h4>
                            @if($group->full_address)
                            <p><i class="fa fa-map-marker"></i> {{ $group->full_address }}</p>
                            @endif
                            @if($group->manager)
                            <p class="mb-0"><i class="fa fa-user"></i> {{ $group->manager->full_name }}</p>
                            @if($group->manager->cell_phone)
                            <p class="mb-0"><i class="fa fa-phone"></i> {{ $group->manager->cell_phone }}</p>
                            @endif
                            @if($group->manager->email_1)
                            <p class="mb-0"><i class="fa fa-envelope"></i> {{ $group->manager->email_1 }}</p>
                            @endif
                            @endif
                            @if($group->description)
                            <h5 class="mt-3">
                                About this group
                            </h5>
                            <p>{!! $group->description !!}</p>
                            @endif
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <i class="fa fa-users text-success fa-lg"></i>
                                    <h5><strong data-groupMembersCount="true">{{ $total }}</strong> Members</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5>Group Share Link</h5>
                        <hr>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-globe"></i></span>
                            </div>
                            <input type="text" id="group_public_link_{{ $group->id }}" class="form-control" readonly="true" value="{{ route('join.show', ['id' => $group->uuid]) }}"/>
                            <div class="input-group-append">
                                <button class="btn btn-outline-info" type="button" onclick="copy('group_public_link_{{ $group->id }}')">
                                    <i class="fa fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($group->manager)
                <div class="card">
                    <div class="card-body">
                        <h5>Group Leader</h5>
                        <hr>
                        @include('people.contacts.includes.contact-profile-short', ['contact' => $group->manager])
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $showMembers ? 'show active' : '' }}" id="group-members" role="tabpanel" aria-labelledby="group-members-tab">
        @include('people.groups.members')
    </div>

    @if (false)
    <div class="tab-pane fade" id="group-events" role="tabpanel" aria-labelledby="group-events-tab">
        @include('people.groups.includes.events')
    </div>
    @endif
</div>

@include('people.groups.includes.share-group-modal')
@include('checkin.includes.attendance-report-options')
@if (auth()->user()->can('group-update'))
@include('people.groups.includes.members-edit-modal')
@endif

@push('scripts')
<script>
    @if (session('message'))
        Swal.fire("{{ session('message') }}", '', 'success');
    @endif

    function manageMembers() {
        $('#searchContacts').keyup();
    }
</script>
@endpush

@endsection
