{{-- TODO this is currently structured so that there is an edit modal created for every task. Consider revising so only one is necessary --}}
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

<div class="modal fade" id="task-modal-{{ array_get($task, 'id') }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Edit Task')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::model($task, ['route' => ['tasks.update', array_get($task, 'id')], 'method' => 'PUT', 'class' => 'task-form']) }}
            {{ Form::hidden('uid', Crypt::encrypt(array_get($task, 'id'))) }}
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span> {{ Form::label('name') }}
                    {{ Form::text('name', array_get($task, 'name'), ['class' => 'form-control', 'autocomplete' => 'off', 'required' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::label('description') }}
                    {{ Form::textarea('description', array_get($task, 'description'), ['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <span class="text-danger">*</span> {{ Form::label('due') }}
                            {{ Form::text('due_date', \Carbon\Carbon::parse(array_get($task, 'due'))->toDateString(), ['class' => 'form-control datepicker', 'required' => true]) }}
                        </div>
                        <div class="col-sm-2">
                            @php $time = displayLocalDateTime(array_get($task, 'due')) @endphp

                            {{ Form::label('time') }}
                            {{ Form::select('hour', $hours, array_get($task, 'show_time') ? $time->format('g') : null, ['class' => 'form-control edit-time']) }}
                        </div>
                        <div class="col-sm-2">
                            {{ Form::label('&nbsp;') }}
                            {{ Form::select('minutes', $minutes, array_get($task, 'show_time') ? $time->format('i') : null, ['class' => 'form-control time']) }}
                        </div>
                        <div class="col-sm-2">
                            {{ Form::label('&nbsp;') }}
                            {{ Form::select('when', ['AM' => 'AM', 'PM' => 'PM'], array_get($task, 'show_time') ? $time->format('A') : null, ['class' => 'form-control time']) }}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span> {{ Form::label('assignee') }}
                    {{ Form::text('assigned_to_contact', $assigned_to, ['class' => 'form-control assign']) }}
                    {{ Form::hidden('assigned_to', array_get($task, 'assigned_to')) }}
                </div>
                <div class="form-group">
                    <div class="d-flex">
                        <label class="c-switch-sm c-switch c-switch-label c-switch-primary mr-2">
                            <input type="checkbox" name="email_assignee_due" value="1" @if(array_get($task, 'email_due')) checked="" @endif class="pull-left c-switch-input" id="email_assignee_due" />
                            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
                        </label>
                        <label for="email_assignee_due">
                            @lang('Email assignee when task is near due date')
                        </label>
                    </div>
                </div>
                <div class="form-group @if(!array_get($task, 'email_due')) d-none @endif emailDueContainer">
                    <div class="d-flex">
                        <label>Email</label>
                        <input type="number" class="form-control small mx-2" name="due_number" value="{{ array_get($task, 'due_number', 1) }}" style="width: 100px;" />
                        <select class="form-control small mr-2" name="due_period" style="width: 100px;">
                            <option value="day" @if(array_get($task, 'due_period', 'day') === 'day') selected @endif>days</option>
                            <option value="week" @if(array_get($task, 'due_period') === 'week') selected @endif>weeks</option>
                            <option value="month" @if(array_get($task, 'due_period') === 'month') selected @endif>months</option>
                        </select>
                        <label>before</label>
                    </div>
                </div>
                <div class="form-group">
                    @if(!isset($contact))
                    <span class="text-danger">*</span> {{ Form::label('link_to_contact') }}
                    {{ Form::text('link_to_contact', $linked_to, ['class' => 'form-control link']) }}
                    {{ Form::hidden('linked_to', array_get($task, 'linked_to')) }}
                    @else
                    {{ Form::hidden('linked_to', array_get($contact, 'id')) }}
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    @lang('Save')
                </button>
                {{ Form::close() }}
                
                @if(array_get($task, 'status') == 'open')
                {{ Form::model($task, ['route' => ['tasks.update', array_get($task, 'id')], 'method' => 'PUT']) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($task, 'id'))) }}
                {{ Form::hidden('complete', 1) }}
                <button type="submit" class="btn btn-success">
                    @lang('Complete Task')
                </button>
                {{ Form::close() }}
                @else
                {{ Form::model($task, ['route' => ['tasks.update', array_get($task, 'id')], 'method' => 'PUT']) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($task, 'id'))) }}
                {{ Form::hidden('open', 1) }}
                <button type="submit" class="btn btn-success">
                    @lang('Reopen Task')
                </button>
                {{ Form::close() }}
                @endif

                {{ Form::open(['route' => ['tasks.destroy', array_get($task, 'id')], 'method' => 'DELETE']) }}
                {{ Form::hidden('uid', Crypt::encrypt(array_get($task, 'id'))) }}
                <button type="submit" class="btn btn-link text-danger">
                    <span class="fa fa-trash-o"></span>
                </button>
                {{ Form::close() }}
                <button type="button" class="btn btn-secondary close-task-modal" data-dismiss="modal">
                    @lang('Close')
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
    <script>
    (function(){
        $('#task-modal-{{ array_get($task, 'id') }} .btn.close-task-modal').on('click', function (e) {
            $('#task-modal-{{ array_get($task, 'id') }} form').get(0).reset()
        });
    }());
    </script>
@endpush
