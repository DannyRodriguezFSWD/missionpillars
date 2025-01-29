@php
$hours = [
    '---' => '---',
    '01' => '01',
    '02' => '02',
    '03' => '03',
    '04' => '04',
    '05' => '05',
    '06' => '06',
    '07' => '07',
    '08' => '08',
    '09' => '09',
    '10' => '10',
    '11' => '11',
    '12' => '12'
 ];
$minutes = [
    '00' => '00',
    '05' => '05',
    '10' => '10',
    '15' => '15',
    '20' => '20',
    '25' => '25',
    '30' => '30',
    '35' => '35',
    '40' => '40',
    '45' => '45',
    '50' => '50',
    '55' => '55'
];
@endphp
{{ Form::open(['route' => 'tasks.store','class' => 'task-form', 'id'=>'create-task-form']) }}
<div class="modal fade" id="add-task-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Add Task')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span> {{ Form::label('name') }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'off', 'required' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::label('description') }}
                    {{ Form::textarea('description', null, ['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <span class="text-danger">*</span> {{ Form::label('due') }}
                            {{ Form::text('due', null, ['class' => 'form-control datepicker', 'required' => true, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="col-sm-2">
                            {{ Form::label('time') }}
                            {{ Form::select('hour', $hours, null, ['class' => 'form-control edit-time']) }}
                        </div>
                        <div class="col-sm-2">
                            {{ Form::label('&nbsp;') }}
                            {{ Form::select('minutes', $minutes, null, ['class' => 'form-control time']) }}
                        </div>
                        <div class="col-sm-2">
                            {{ Form::label('&nbsp;') }}
                            {{ Form::select('when', ['AM' => 'AM', 'PM' => 'PM'], null, ['class' => 'form-control time']) }}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span> {{ Form::label('assignee') }}
                    {{ Form::text('assigned_to_contact', null, ['class' => 'form-control assign']) }}
                    {{ Form::hidden('assigned_to', null) }}
                </div>
                <div class="form-group">
                    <div class="d-flex">
                        <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                            <input type="checkbox" name="email_assignee" value="1" class="pull-left c-switch-input" id="email_assignee" />
                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                        </label>
                        <label for="email_assignee">
                            @lang('Email assignee when task is created')
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="d-flex">
                        <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                            <input type="checkbox" name="email_assignee_due" value="1" class="pull-left c-switch-input" id="email_assignee_due" />
                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                        </label>
                        <label for="email_assignee_due">
                            @lang('Email assignee when task is near due date')
                        </label>
                    </div>
                </div>
                <div class="form-group d-none emailDueContainer">
                    <div class="d-flex">
                        <label>Email</label>
                        <input type="number" class="form-control small mx-2" name="due_number" value="1" style="width: 100px;" />
                        <select class="form-control small mr-2" name="due_period" style="width: 100px;">
                            <option value="day" selected>days</option>
                            <option value="week">weeks</option>
                            <option value="month">months</option>
                        </select>
                        <label>before</label>
                    </div>
                </div>
                <div class="form-group">
                    @if(!isset($contact))
                    <span class="text-danger">*</span> {{ Form::label('link_to_contact') }}
                    {{ Form::text('link_to_contact', null, ['class' => 'form-control link']) }}
                    {{ Form::hidden('linked_to', null) }}
                    @else
                    {{ Form::hidden('linked_to', array_get($contact, 'id')) }}
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    @lang('Close')
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
{{ Form::close() }}


@push('scripts')
    <script>
    (function(){
        $('#task-modal').on('hidden.coreui.modal', function (e) {
            $('#create-task-form').get(0).reset()
        });
    }());
    </script>
@endpush
