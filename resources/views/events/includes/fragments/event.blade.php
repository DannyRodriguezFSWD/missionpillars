<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>Event Basic Information</h5>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('name', 'Event Name') }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Event Name', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('name', __('Manager\'s Name (Who is managing this event?)')) }}
                    {{ Form::text('manager', array_get($manager, 'name'), ['class' => 'form-control autocomplete', 'placeholder' => 'Manager\'s Name', 'required' => true, 'autocomplete' => 'off']) }}
                    {{ Form::hidden('contact_id', array_get($manager, 'id')) }}
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('description', 'Description') }}
                    {{ Form::textarea('description', null, ['class' => 'form-control tinyTextarea', 'rows' => 2]) }}
                    {{ Form::hidden('content') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>Cover Image</h5>
            </div>
            <div class="col-12">
                    <div class="m-4 text-center">
                        <label for="image">
                            <img id="renderImage" onmouseenter="$(this).css('opacity','.8')" onmouseout="$(this).css('opacity','1')"
                                 src="{{ array_get($event, 'img_cover') ? asset('storage/event_images/'.array_get($event, 'img_cover')) : asset('img/blank_placeholder.png') }}"
                                 class="img-responsive p-1" style="max-height: 35vh; border: 1px dashed black; cursor:pointer;"/>
                        </label>
                        <h4 class="text-center">Drop an Image or Click to Upload.</h4>
                        <button type="button" class="btn btn-sm btn-secondary d-none" id="unsetImage"><i class="fa fa-undo"></i></button>
                        @if(request()->routeIs('events.settings') && array_get($event, 'img_cover'))
                            <button type="button" class="btn btn-sm btn-danger" id="removeImage"><i class="fa fa-trash"></i> Remove Cover Image</button>
                        @endif
                    </div>

                <div class="form-group">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" data-render-to=".eventImageUpload" type="file" id="image">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" name="image" data-render-to=".eventImageUpload" type="file" id="image2">
                    <input type="hidden" name="removeCoverImage">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5> Date &amp; Time </h5>
        <div class="row">
            <div class="form-group col-md-10 col-lg-8" id="full-day">
                {{ Form::label('event_date', 'Event Date') }}
                {{ Form::text('event_date', isset($split) ? date('Y-m-d', strtotime(displayLocalDateTime(array_get($split, 'start_date'),array_get($split, 'template.timezone')))) : $date, ['class' => 'form-control datepicker readonly']) }}
            </div>

            <div id="not-full-day" class="col-lg-10 col-xl-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('start_date', 'Event Starts On') }}<br/>
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                @if( !is_null(array_get($split, 'start_date')) )

                                        {{ Form::text('start_date',
                                        displayLocalDateTime(array_get($split, 'start_date'),array_get($split, 'template.timezone'))
                                        ->toDateString(),
                                        ['class' => 'datepicker readonly form-control', 'autocomplete' => 'off'])
                                        }}
                                @else
                                        {{ Form::text('start_date', $start_date,
                                        ['class' => 'datepicker readonly form-control', 'autocomplete' => 'off'])
                                        }}

                                @endif
                                </div>
                                <div class="col-md-6 mb-1">
                                    <select id="start_time" name="start_time" class="form-control">
                                        @include('events.includes.time-options')
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('end_date', 'Event Ends On') }}<br/>
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    @if( !is_null(array_get($split, 'end_date')) )
                                        {{ Form::text('end_date', displayLocalDateTime(array_get($split, 'end_date'),array_get($split, 'template.timezone'))->toDateString(), ['class' => 'form-control datepicker readonly', 'autocomplete' => 'off']) }}
                                    @else
                                        {{ Form::text('end_date', $end_date, ['class' => 'datepicker readonly form-control', 'autocomplete' => 'off']) }}
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <select id="end_time" name="end_time" class="form-control">
                                        @include('events.includes.time-options')
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-xl-4 mb-1">
                <label for="timezone">@lang('Timezone')</label>
                @include('_partials.timezone')
            </div>
            <div class="col-12 mb-3">
                <div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="" id="calendar-color" style="width: 40px">&nbsp;&nbsp;&nbsp;</span>
                        </div>
                        <select class="form-control" name="calendar_id">
                            @foreach($calendars as $calendar)
                                <option value="{{ array_get($calendar, 'id') }}" data-background="{{ array_get($calendar, 'color') }}" {{ isset($calendar_id) && $calendar->id == $calendar_id ? 'selected' : '' }}>{{ array_get($calendar, 'name') }}</option>
                            @endforeach
                        </select>
                        @if(isset($calendar_id))
                            @push('scripts')
                                <script type="text/javascript">
                                    (function () {
                                        $('#new-event button:first').trigger('click');
                                    })();
                                </script>
                            @endpush
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                        <input id="is_all_day" name="is_all_day" type="checkbox" class="c-switch-input" value="1">
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                    </label>
                    <label for="is_all_day">@lang('All Day')</label>
                </div>
            </div>
            @if( array_get($event, 'repeat') === 1 )
                <div class="col-sm-12 alert-schedule">
                    @lang('Event Repeats'): {{ array_get($event, 'repeat_cycle') }}
                    <button type="button" class="btn btn-link" data-toggle="modal" data-target="#alert-change-recursion">Change</button>
                </div>
            @endif
            <div class="col-md-6 schedule">
                <div class="d-flex">
                    <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                        <input id="event_repeats" name="event_repeats" type="checkbox" class="c-switch-input">
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                    </label>
                    <label for="event_repeats">@lang('Event Repeats')</label>
                </div>
            </div>
        </div>
        <div id="event-repeats">
            <p>&nbsp;</p>
            <h5>Recurrence Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <br>
                    <p>@lang('Repeat')</p>
                    <div class="form-group">
                        {{ Form::select('repeat_cycle', ['Daily' => 'Daily','Weekly' => 'Weekly','Monthly' => 'Monthly','Yearly' => 'Yearly'], null, ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('repeat_every', 'Repeats every') }}
                        {{ Form::number('repeat_every', isset($event) ? null : 1, ['class' => 'text-center form-control w-25 d-inline', 'min' => 1, 'max' => 30, 'step' => 1, 'autocomplete' => 'off']) }}
                        <span id="text">@lang('Days')</span>
                    </div>
                </div>
                {{ Form::hidden('rescheduled', 0) }}
                <div class="col-md-6">
                    <p>@lang('Ends')</p>
                    <div class="form-group">
                        {{ Form::radio('ends_on', 'Never', true, ['id' => 'Never']) }} <span style="display:inline-block; width: 6ch">@lang('Never')</span><br/>
                    </div>
                    <div class="form-group">
                        {{ Form::radio('ends_on', 'After', false, ['id' => 'After']) }} <span style="display:inline-block; width: 6ch">@lang('After')</span> {{ Form::number('ends_on_occurrences', array_get($event, 'repeat_occurrences', 1), ['class' => 'text-center form-control w-25 d-inline', 'min' => 1, 'max' => 365, 'step' => 1, 'autocomplete' => 'off']) }} @lang('occurrences')
                    </div>
                    <div class="form-group">
                        {{ Form::radio('ends_on', 'On', false, ['id' => 'On']) }} <span style="display:inline-block; width: 6ch">@lang('On')</span> {{ Form::text('ends_on_date', isset($event) && $event->repeat_ends_on ? date('Y-m-d', strtotime($event->repeat_ends_on)) : date('Y-m-d'), ['class' => 'datepicker readonly form-control w-25 d-inline']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>Event Location</h5>
        <div class="row">
            <div class="col-sm-12">
                @includeIf('events.includes.fragments.location')
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div role="button" class="btn btn-link pl-0" data-toggle="modal" data-target="#capture_signup_info">
            <h5>
                Choose How To Capture Sign-up Info
                <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;"></i>
            </h5>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <input {{ array_get($event, 'version') == 2 ? 'checked':'' }} type="radio" name="version" required value="2" id="v1">
                    <label for="v1">First name, last name, email form</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <input {{ ((array_get($event, 'version') != 2 && isset($event)) || isset($groupEvent)) ? 'checked':'' }} type="radio" name="version" value="1" id="v2">
                    <label for="v2">Public directory search</label>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="d-flex">
                    <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                        @if( array_get($event, 'allow_auto_check_in') )
                            <input type="checkbox" name="allow_auto_check_in" value="1" checked="" class="pull-left c-switch-input" id="allow_auto_check_in"/>
                        @else
                            <input type="checkbox" name="allow_auto_check_in" value="1" class="pull-left c-switch-input" id="allow_auto_check_in"/>
                        @endif
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                    </label>
                    <label for="allow_auto_check_in">
                        @lang('Automatically check-in on registration')
                    </label>
                </div>
            </div>
            <div class="col-12 mt-2">
                <div class="form-group">
                    {{ Form::label('group_id', __('What group is this event for')) }}
                    {{ Form::select('group_id', $groups, array_get($split, 'template.group_id', $groupId), ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-12 mt-2">
                <div class="d-flex">
                    <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                        <input id="remind_manager" name="remind_manager" type="checkbox" class="c-switch-input">
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                    </label>
                    <label for="remind_manager">@lang('Remind event manager to checkin people')</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body px-sm-4 px-1">
        <h5>Event Sign Up & Ticket Option</h5>
        <div class="row">
            <div class="col-sm-12 my-2">
                <div class="d-flex">
                    <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                        @if( array_get($event, 'allow_reserve_tickets') )
                            <input type="checkbox" name="allow_reserve_tickets" value="1" checked="" class="c-switch-input" id="allow_reserve_tickets"/>
                        @else
                            <input type="checkbox" name="allow_reserve_tickets" value="1" class="c-switch-input" id="allow_reserve_tickets"/>
                        @endif
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                    </label>
                    <label for="allow_reserve_tickets">
                        @lang('Attenders must reserve tickets')
                    </label>
                </div>
            </div>
            
            <div class="col-12">
                <div class="row justify-content-center" id="allow_reserve_tickets_settings" style="display: none;">
                    <hr/>
                    <div class="col-sm-12">
                        <h5>@lang('Ticket Settings')</h5>
                    </div>
                    
                    <div class="col-sm-12 my-2" id="is_paid_event" style="display: none;">
                        <div class="d-flex">
                            <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                                @if( array_get($event, 'is_paid') )
                                    <input type="checkbox" name="is_paid" value="1" checked="" class="c-switch-input" id="is_paid"/>
                                @else
                                    <input type="checkbox" name="is_paid" value="1" class="c-switch-input" id="is_paid"/>
                                @endif
                                <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                            </label>
                            <label for="is_paid">
                                @lang('This is a paid event')
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-sm-12 my-2">
                        <div class="d-flex">
                            <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2" style="min-width: 40px;">
                                @if( array_get($event, 'pay_later') )
                                    <input type="checkbox" name="pay_later" value="1" checked="" class="c-switch-input" id="pay_later"/>
                                @else
                                    <input type="checkbox" name="pay_later" value="1" class="c-switch-input" id="pay_later"/>
                                @endif
                                <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                            </label>
                            <label for="pay_later">
                                @lang('Allow people to claim tickets then pay later')
                                <i class="fa fa-question-circle-o text-info" data-toggle="tooltip" data-placement="right" title="@lang('During ticket purchase, they will be prompted to pay for the ticket right away, but if they do not, can they pay later?')"></i>
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-sm-4 text-right d-none">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#ticket-option-modal">
                            <span class="fa fa-plus-circle"></span>
                            @lang('Add Ticket Option')
                        </button>
                    </div>
                    <div class="col-sm-11">
                        <style>
                            #ticket-options td {
                                padding: 3px;
                                border-top: 1px solid #e8eff2;
                            }
                        </style>
                        <table class="table" id="ticket-options">
                            <thead>
                            <tr>
                                <td width="35%">@lang('Ticket Name')</td>
                                <td width="30%">@lang('Ticket Price')</td>
                                <td width="30%">@lang('Number of available tickets')</td>
                                <td width="4%">&nbsp;</td>
                            </tr>
                            </thead>
                            <tbody>
                            @if(!is_null($event))
                                @foreach( array_get($event, 'ticketOptions') as $option)
                                    <tr>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text d-sm-down-none"><i class="fa fa-ticket"></i></span>
                                                </div>
                                                <input type="text" name="ticket_name[]" value="{{ array_get($option, 'name') }}" class="ticket-option form-control">
                                            </div>
                                            <input type="hidden" name="ticket_record[]" value="{{ array_get($option, 'id') }}"/>
                                            {{ Form::hidden('is_free_ticket[]', array_get($option, 'is_free_ticket', 0)) }}
                                            {{ Form::hidden('allow_unlimited_tickets[]', array_get($option, 'allow_unlimited_tickets', 0)) }}
                                        </td>
                                        <td>
                                            @if (array_get($option, 'is_free_ticket', 0) == 1)
                                                <div class="badge-pill badge-info p-2 text-white text-center">
                                                    Free
                                                    <input type="hidden" name="ticket_price[]" value="0" class="ticket-option form-control">
                                                </div>
                                            @else
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text d-sm-down-none"><i class="fa fa-dollar"></i></span>
                                                    </div>
                                                    <input type="number" step="0.01" name="ticket_price[]" value="{{ array_get($option, 'price') }}" class="ticket-option form-control">
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if (array_get($option, 'allow_unlimited_tickets', 0) == 1)
                                                <div class="badge-pill badge-warning p-2 text-white text-center">
                                                    Unlimited
                                                    <input type="hidden" name="ticket_availability[]" value="0" class="ticket-option form-control">
                                                </div>
                                            @else
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text d-sm-down-none">
                                                            <i class="fa fa-hashtag" aria-hidden="true"></i>
                                                        </span>
                                                    </div>
                                                    <input type="number" name="ticket_availability[]" value="{{ array_get($option, 'availability', 0) }}" class="ticket-option form-control">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text px-1">
                                                            / {{$option->totalNumberOfTickets}}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif

                                        </td>
                                        <td class="text-right">
                                            <!-- DO NOT DELETE TICKETS -->
                                            <button type="button" class="delete-ticket-option btn btn-link px-0">
                                                <span class="fa fa-minus-circle text-danger"></span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3">
                                    <button type="button" class="btn btn-success mt-4" data-toggle="modal" data-target="#ticket-option-modal">
                                        <span class="fa fa-plus-circle"></span>
                                        @lang('Add Ticket Option')
                                    </button>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="row" id="paid_event_settings">
                    <hr>
                    <div class="col-sm-12">
                        <h5>@lang('Payment Settings')</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('campaign_id', 'Fundraiser') }}
                            {{ Form::select('campaign_id', $campaigns, null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('purpose_id', 'Purpose') }}
                            @if ($errors->has('purpose_id'))
                                <span class="help-block text-danger">
                    <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
                </span>
                            @endif
                            {{ Form::select('purpose_id', $charts, null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex">
                            <label class="c-switch c-switch-label c-switch-sm c-switch-primary mr-2">
                                @if(isset($event) && array_get($event, 'tax_deductible'))
                                    <input type="checkbox" name="tax_deductible" checked="true" class="c-switch-input" value="1">
                                @else
                                    <input type="checkbox" name="tax_deductible" class="c-switch-input" value="1">
                                @endif
                                <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                            </label>
                            {{ Form::label('tax_deductible', 'Tax Deductible') }}
                        </div>
                    </div>
                </div>
                <div class="row my-2" id="whose_ticket" style="display: none;">
                    <div class="col-sm-12">
                        <div class="d-flex">
                            <input type="hidden" name="ask_whose_ticket" value="0" class="c-switch-input" id="ask_whose_ticket"/>
                            <label class="c-switch c-switch-sm c-switch-label c-switch-primary mr-2">
                                @if( array_get($event, 'ask_whose_ticket') )
                                    <input type="checkbox" name="ask_whose_ticket" value="1" checked="" class="c-switch-input" id="ask_whose_ticket"/>
                                @else
                                    <input type="checkbox" name="ask_whose_ticket" value="1" class="c-switch-input" id="ask_whose_ticket"/>
                                @endif
                                <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                            </label>
                            <label for="ask_whose_ticket">
                                @lang('Ask user to fill first name, last name and email for each ticket')
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>@lang('Custom Header for Confirmation Email')</h5>
        <div class="form-group">
            {{ Form::label('custom_header', 'Email Header (optional)') }}
            <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip" data-placement="right" title="If provided, the text you enter here will be displayed at the top of the email sent to the registrant"></i>
            {{ Form::textarea('custom_header', null, ['class' => 'form-control tinyTextarea', 'rows' => 2]) }}
            {{ Form::hidden('custom_header') }}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @includeIf('events.includes.new-event-right')
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>
            @lang('Custom Landing Page')
        </h5>
        <div class="form-group">
    <span>
        @lang('Custom URL')
            <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip"
               data-placement="right"
               title="You can enter a custom URL if you want users redirected somewhere specific after completion. Full URL is required (examples: https://www.example.com, https://example.com)"></i>
    </span>
            <p>*Note: If you are using a custom form, this will be ignored, and the custom landing page of the form will be used instead. </p>
            {{ Form::text('custom_landing_page', array_get($event, 'custom_landing_page'), ['class' => 'form-control', 'placeholder' => 'Custom URL', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>


{{-- Modals --}}
<div class="modal" id="capture_signup_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Choose How To Capture Sign-up Info')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('For example, if you are a church, you may want to choose the public directory search so people can find themselves without typing in all info.').</p>
                <p>@lang('If this is a larger public event, just have people type in their info to sign up.')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="cropperModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crop Image to 16 / 9 Ratio</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <img src="" class="eventImageUpload img-fluid" id="cropperRenderImage" alt="">
                    </div>
                    <div class="col-12 text-center mt-2">
                        <input type="range" step="0.1" min="0" max="4" id="zoomRange" class="form-control-range">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="saveCropImage" type="button" class="btn btn-success">Ok</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>
@include('events.includes.alert-change-recursion-modal')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.css" integrity="sha512-Aix44jXZerxlqPbbSLJ03lEsUch9H/CmnNfWxShD6vJBbboR+rPdDXmKN+/QjISWT80D4wMjtM4Kx7+xkLVywQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.js" integrity="sha512-E4KfIuQAc9ZX6zW1IUJROqxrBqJXPuEcDKP6XesMdu2OV4LW7pj8+gkkyx2y646xEV7yxocPbaTtk2LQIJewXw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        let fileImage = null;
        (function () {
            let imageInput = document.getElementById('image')
            let dropContainer = document.getElementById('renderImage')
            dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
                evt.preventDefault();
                dropContainer.classList.add('drop-active')
            };

            ['dragleave','dragend'].forEach(ev => {
                dropContainer.addEventListener(ev,function (evt) {
                    dropContainer.classList.remove('drop-active')
                })
            })

            dropContainer.ondrop = function(evt) {
                dropContainer.classList.remove('drop-active')
                if (document.getElementById('image').accept.split(', ').indexOf(evt.dataTransfer.files[0].type) == -1){
                    Swal.fire('Invalid Image','Please drop a valid image','info')
                    return false
                }
                imageInput.files = evt.dataTransfer.files;
                $(imageInput).trigger('input')

                evt.preventDefault();
            };

            let $modal = $('#cropperModal');
            let image = document.getElementById('cropperRenderImage');
            let cropper;
            let file;
            $("#image").on("input change", function(e){
                let files = e.target.files;
                let done = function(url) {
                    image.src = url;
                    $modal.modal('show');
                };
                let reader;
                if (files && files.length > 0) {
                    file = files[0];
                    if (isValidFileImage(file) == false) {
                        Swal.fire('Invalid Image', 'Please select a valid image', 'info')
                        return false
                    }
                    if (URL) {
                        done(URL.createObjectURL(file));
                    } else if (FileReader) {
                        reader = new FileReader();
                        reader.onload = function(e) {
                            done(reader.result);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
            $modal.on('shown.coreui.modal', function() {
                cropper = new Cropper(image, {
                    aspectRatio: 16 / 9,
                    viewMode: 1,
                });
                document.getElementById('zoomRange').value = 1;
                document.getElementById('zoomRange').addEventListener('input',function (e) {
                    console.log(e.target.value)
                    cropper.zoomTo(e.target.value)
                })
            }).on('hide.coreui.modal', function(e) {
                if (document.activeElement.id != 'saveCropImage') document.getElementById('image').value = '';
                cropper.destroy();
                cropper = null;
            });
            $("#saveCropImage").on("click", function() {
                canvas = cropper.getCroppedCanvas();
                canvas.toBlob(function(blob) {
                    fileImage = new File([blob], file.name,{type:file.type, lastModified:new Date().getTime()});
                    document.querySelector('[name="removeCoverImage"]').value = ''
                    let reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function() {
                        let base64data = reader.result;
                        document.getElementById('renderImage').setAttribute('src',base64data) ;
                        $modal.modal('hide');
                        $('#unsetImage').removeClass('d-none')
                    }
                },file.type);
            })
        })()
    </script>

    <script>
        (function () {
            let oldImage = "{{ array_get($event, 'img_cover') ? asset('storage/event_images/'.array_get($event, 'img_cover')) : asset('img/blank_placeholder.png') }}"
            document.getElementById('unsetImage').addEventListener('click',function () {
                fileImage = null;
                document.getElementById('renderImage').setAttribute('src',oldImage)
                document.querySelector('[name="removeCoverImage"]').value = ''
                $('#unsetImage').addClass('d-none')
                if ($('#removeImage')) $('#removeImage').removeClass('d-none');
            })
            @if(request()->routeIs('events.settings'))
            let defaultBlankImage = "{{asset('img/blank_placeholder.png') }}";
            document.getElementById('removeImage').addEventListener('click',function () {
                fileImage = null;
                document.querySelector('[name="removeCoverImage"]').value = '1'
                document.getElementById('renderImage').setAttribute('src',defaultBlankImage)
                $('#unsetImage').removeClass('d-none')
                $('#removeImage').addClass('d-none')
            })
            @endif
        })()
    </script>

    <script>
    initTinyEditor();

    (function(){
        document.getElementById('image').addEventListener('input',function (e) {
            renderImage(e.target)
        })
        $('#form').on('submit', function (e) {
            let markupStr = tinymce.get("description").getContent();

            if (markupStr === '') {
                Swal.fire('Enter the description field','','info');
                return false;
            }
            let markupStr_custom_header = tinymce.get("custom_header").getContent();
            $("input[name='content']").val(markupStr);
            $("input[name='custom_header']").val(markupStr_custom_header);

            let fData = new FormData(document.getElementById('form'))
            if (fileImage) fData.set('image',fileImage);
            $('#overlay').show()
            axios({
                url: document.getElementById('form').getAttribute('action'),
                method: "POST",
                data: fData,
                headers: { "Content-Type": "multipart/form-data" },
            }).then(function(response){
                Swal.fire('Success!',response.data.message,'success');
                if (response.data.redirect) window.location.href = response.data.redirect;
                else window.location.reload();
            }).catch(function (err){
                let message = Object.values(err.response.data).join('<br>')
                Swal.fire('Oops!',message,'info');
            }).finally(function () {
                $('#overlay').hide()
            })
            return false;
            e.preventDefault()
        });
    })();
    
    $(document).ready(function () {
        $('[name="manager"]').prop('autocomplete', 'none');
    });
</script>
@endpush
