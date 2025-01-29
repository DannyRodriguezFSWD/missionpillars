<h5>@lang('Event Settings')</h5>
<div class="row">
    <div class="col-sm-2">
        <br>
        <span class="badge p-3" id="calendar-color">&nbsp;&nbsp;&nbsp;</span>
    </div>
    <div class="col-sm-10">
        <div class="form-group">
    <span class="text-danger">*</span> 
    {{ Form::label('calendar', 'Calendar') }}
    {{-- Form::select('calendar_id', $calendars, isset($calendar_id) ? $calendar_id : null, ['class' => 'form-control']) --}}
    
    <select class="form-control" name="calendar_id">
        @foreach($calendars as $calendar)
        <option value="{{ array_get($calendar, 'id') }}" data-background="{{ array_get($calendar, 'color') }}" {{ isset($calendar_id) && $calendar->id == $calendar_id ? 'selected' : '' }}>{{ array_get($calendar, 'name') }}</option>
        @endforeach
    </select>
    @if(isset($calendar_id))
        @push('scripts')
        <script type="text/javascript">
            (function(){
                $('#new-event button:first').trigger('click');
            })();
        </script>
        @endpush
    @endif
</div>
    </div>
</div>
<div class="form-group">
    <span class="text-danger">*</span> 
    {{ Form::label('name', 'Name') }}
    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Event Name', 'required' => true, 'autocomplete' => 'off']) }}
</div>
<div class="form-group">
    {{ Form::label('description', 'Description') }}
    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) }}
</div>

<div class="form-group" id="full-day">
    {{ Form::label('event_date', 'Event Date') }}
    {{ Form::text('event_date', isset($event) ? date('Y-m-d', strtotime($event->start)) : date('Y-m-d'), ['class' => 'form-control datepicker readonly']) }}
</div>

<div class="row" id="not-full-day">
    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::label('start_date', 'Starts On') }}<br/>
            {{ Form::text('start_date', isset($event) ? date('Y-m-d', strtotime($event->start)) : date('Y-m-d'), ['class' => 'datepicker readonly', 'autocomplete' => 'off']) }}
            <select id="start_time" name="start_time">
                @include('events.includes.time-options')
            </select>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::label('end_date', 'Ends On') }}<br/>
            {{ Form::text('end_date', isset($event) ? date('Y-m-d', strtotime($event->end)) : date('Y-m-d'), ['class' => 'datepicker readonly', 'autocomplete' => 'off']) }}
            <select id="end_time" name="end_time">
                @include('events.includes.time-options')
            </select>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-sm-12">
        {{ Form::checkbox('is_all_day', true, true) }} @lang('All Day') <br/>
        {{ Form::checkbox('event_repeats', true) }} @lang('Event Repeats')
    </div>
</div>

<div id="event-repeats" class="row">
    <div class="col-sm-12">
        <br>
        <h5>@lang('Repeat')</h5>
        <div class="form-group">
            {{ Form::select('repeat_cycle', ['Daily' => 'Daily','Weekly' => 'Weekly','Monthly' => 'Monthly','Yearly' => 'Yearly'], null, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::label('repeat_every', 'Repeats every') }}
            {{ Form::number('repeat_every', isset($event) ? null : 1, ['class' => 'text-center', 'min' => 1, 'max' => 30, 'step' => 1, 'autocomplete' => 'off']) }} 
            <span id="text">@lang('Days')</span>
        </div>
    </div>


    <div class="col-sm-12">
        <p>@lang('Ends')</p>
        <div class="form-group">
            {{ Form::radio('ends_on', 'Never', true, ['id' => 'Never']) }} @lang('Never')<br/>
        </div>
        <div class="form-group">
            {{ Form::radio('ends_on', 'After', false, ['id' => 'After']) }} @lang('After') {{ Form::number('ends_on_occurrences', 1, ['class' => 'text-center', 'min' => 1, 'max' => 365, 'step' => 1, 'autocomplete' => 'off']) }} @lang('occurrences')
        </div>
        <div class="form-group">
            {{ Form::radio('ends_on', 'On', false, ['id' => 'On']) }} @lang('On') {{ Form::text('ends_on_date', isset($event) && $event->repeat_ends_on ? date('Y-m-d', strtotime($event->repeat_ends_on)) : date('Y-m-d'), ['class' => 'datepicker readonly']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h5>&nbsp;</h5>
        <h5>@lang('Location')</h5>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        @if ($errors->has('mailing_address_1'))
        <span class="help-block">
            <small><strong>{{ $errors->first('mailing_address_1') }}</strong></small>
        </span>
        @endif
        <div class="form-group {{$errors->has('mailing_address_1') ? 'has-danger':''}}">
            {{ Form::label('mailing_address_1', __('Address')) }}
            {{ Form::text('mailing_address_1', isset($event) && $event->addressInstance->first() ? $event->addressInstance->first()->mailing_address_1 : null , ['class' => 'form-control', 'placeholder' => __('Adddress'), 'value'=>old('mailing_address_1'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        @if ($errors->has('city'))
        <span class="help-block">
            <small><strong>{{ $errors->first('city') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('city', __('City')) }}
            {{ Form::text('city', isset($event) && $event->addressInstance->first() ? $event->addressInstance->first()->city : null, ['class' => 'form-control', 'placeholder' => __('City'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        @if ($errors->has('region'))
        <span class="help-block">
            <small><strong>{{ $errors->first('region') }}</strong></small>
        </span>
        @endif

        <div class="form-group">
            {{ Form::label('region', __('Region')) }}
            {{ Form::text('region', isset($event) && $event->addressInstance->first() ? $event->addressInstance->first()->region : null, ['class' => 'form-control', 'placeholder' => __('Region'), 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        @if ($errors->has('country'))
        <span class="help-block">
            <small><strong>{{ $errors->first('country') }}</strong></small>
        </span>
        @endif

        <div class="form-group {{$errors->has('country') ? 'has-danger':''}}">
            {{ Form::label('country_id', __('Country')) }}
            {{ Form::select('country_id', $countries, isset($event) && $event->addressInstance->first() ? $event->addressInstance->first()->country_id : null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    (function(){
        //hello 
        $('input[name=is_all_day]').on('click', function (e) {
            var checked = $(this).prop('checked');
            
            if (checked === false) {
                $('#full-day').hide();
                $('#not-full-day').show();
            } else {
                $('#not-full-day').hide();
                $('#full-day').show();
            }
        });
        
        $('input[name=event_repeats]').on('click', function (e) {
            var checked = $(this).prop('checked');
            
            if (checked === true) {
                $('#event-repeats').show();
            } else {
                $('#event-repeats').hide();
            }
        });
        
        $('select[name=repeat]').on('change', function (e) {
            var value = $(this).val();
            switch (value) {
                case 'weekly':
                    $('#text').html('Weeks');
                    break;
                case 'monthly':
                    $('#text').html('Months');
                    break;
                case 'yearly':
                    $('#text').html('Years');
                    break;
                default:
                    $('#text').html('Days');
                    break;
            }
        });
        
        $('select[name=calendar_id]').on('change', function (e) {
            var color = $(this).find(':selected').data('background');
            $('#calendar-color').css({
                background: color
            });
        });
        
        $('select[name=calendar_id]').trigger('change');
        
    })();
</script>
@endpush