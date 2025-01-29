<div class="col-md-12 promote_togglable_div" id="event-settings">
    <div class="alert alert-info">
        <h4 class="mb-0" id="settingsTitle">
            First select the event that you are going to checkin for
        </h4>
    </div>
    
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title font-weight-bold inline_block mb-0">Event Settings</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="dropdown d-inline mr-2">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="eventsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <b>Event:</b>
                            <i class="fa fa-filter"></i>
                            @lang ('Select Event')
                        </button>
                        <div class="dropdown-menu" aria-labelledby="eventsDropdown">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="Search Event" id="searchEvents" autocomplete="off">
                            </div>

                            <div id="eventsContainer" class="overflow-auto" style="max-height: 300px;">
                                @foreach ($events as $event)
                                <a class="dropdown-item" href="#" data-name="{{ array_get($event, 'template.name') }}" data-id="{{ array_get($event, 'uuid') }}" onclick="filterContacts(this, 'event')">
                                    {!! array_get($event, 'template.name', __('Unknown')).' <span class="small ml-2">('.date('D jS M', strtotime(array_get($event, 'start_date'))).')</span>' !!}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @lang('OR')

                    <button class="btn btn-success ml-2" onclick="$('#eventFormContainer').fadeToggle()">
                        <i class="fa fa-plus-circle"></i> Make a new one
                    </button>

                    <div id="eventFormContainer" class="mt-3"  style="display: none;">
                        {{ Form::open(['route' => 'events.store', 'id' => 'eventForm']) }}
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>
                                                @lang('Event Basic Information')
                                                <i class="fa fa-times pull-right cursor-pointer" onclick="$('#eventFormContainer').fadeToggle()"></i>
                                            </h5>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <span class="text-danger">*</span>
                                                {{ Form::label('name', __('Event Name')) }}
                                                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Event Name'), 'required' => true, 'autocomplete' => 'off']) }}
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <span class="text-danger">*</span>
                                                {{ Form::label('description', __('Description')) }}                                    
                                                {{ Form::textarea('description', null, ['class' => 'form-control tinyTextarea', 'rows' => 2]) }}
                                                {{ Form::hidden('content') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {{ Form::label('start_date', __('Event Time')) }}<br/>
                                                <div class="row">
                                                    <div class="col-md-6 mb-1">
                                                        {{ Form::text('start_date', date('Y-m-d'),
                                                        ['class' => 'datepicker readonly form-control', 'autocomplete' => 'off'])
                                                        }}

                                                    </div>
                                                    <div class="col-md-6 mb-1">
                                                        <select id="start_time" name="start_time" class="form-control">
                                                            @include('events.includes.time-options')
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-none">
                                        {{ Form::select('timezone', getAvalableTimezones(), session('timezone'), ['class' => 'form-control']) }}
                                    </div>

                                    <button type="button" class="btn btn-success" onclick="saveEvent(this);">
                                        <i class="fa fa-save"></i> @lang('Save')
                                    </button>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
