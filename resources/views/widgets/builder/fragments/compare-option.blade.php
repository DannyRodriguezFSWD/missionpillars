<div id="compare-option">
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-primary active">
                    {{ Form::radio('compare', 'events', true) }} @lang('Events')
                </label>

                <label class="btn btn-primary">
                    {{ Form::radio('compare', 'forms') }} @lang('Forms')
                </label>

                <label class="btn btn-primary">
                    {{ Form::radio('compare', 'dates') }} @lang('Date')
                </label>
            </div>
        </div>
    </div>

    <div class="row" id="events-option">
        <div class="col-sm-4">
            {{ Form::label('calculate_by', __('Event')) }}
            {{ Form::select('calculate_by', ['attendance' => 'Attendance', 'contacts' => 'Contacts', 'giving' => 'Giving'], null, ['class' => 'form-control']) }}
        </div>
        <div class="col-sm-4 text-center">
            &nbsp;<br>
            VS
        </div>
        <div class="col-sm-4">
            {{ Form::label('calculate_by', __('Event')) }}
            {{ Form::select('calculate_by', ['attendance' => 'Attendance', 'contacts' => 'Contacts', 'giving' => 'Giving'], null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="row" id="forms-option">
        <div class="col-sm-4">
            {{ Form::label('calculate_by', __('Form')) }}
            {{ Form::select('calculate_by', ['attendance' => 'Attendance', 'contacts' => 'Contacts', 'giving' => 'Giving'], null, ['class' => 'form-control']) }}
        </div>
        <div class="col-sm-4 text-center">
            &nbsp;<br>
            VS
        </div>
        <div class="col-sm-4">
            {{ Form::label('calculate_by', __('Form')) }}
            {{ Form::select('calculate_by', ['attendance' => 'Attendance', 'contacts' => 'Contacts', 'giving' => 'Giving'], null, ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="row" id="dates-option">
        <div class="col-sm-4">
            <input type="text" name="date1" class="form-control datepicker" required/>
        </div>
        <div class="col-sm-4 text-center">VS</div>
        <div class="col-sm-4">
            <input type="text" name="date2" class="form-control datepicker" required/>
        </div>
    </div>
</div>