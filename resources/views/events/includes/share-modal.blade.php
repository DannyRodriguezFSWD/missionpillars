<div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span class="fa fa-share-alt"></span>
                    @lang('Share Calendars')
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <nav class="mb-3">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-share" role="tab" aria-controls="nav-home" aria-selected="true">
                            <i class="fa fa-share fa-lg"></i> Share Calendar
                        </a>
                        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-export" role="tab" aria-controls="nav-profile" aria-selected="false">
                            <i class="fa fa-download fa-lg"></i> Export Calendar
                        </a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-share" role="tabpanel" aria-labelledby="nav-share-tab">

                        <div class="form-group">
                            <label>@lang('How should we display your calendar')?</label>
                            <p>
                                <input type="radio" name="display_calendar_view" value="calendar" checked/> @lang('Calendar View')
                                <br/>
                                <input type="radio" name="display_calendar_view" value="list"/> @lang('List View')
                            </p>
                        </div>
                        <div class="form-group">
                            <label>@lang('Select which calendars want to share')</label>
                            <div style="max-height: 300px; overflow: auto;">
                                <ul class="list-group">
                                    @foreach($calendars as $calendar)
                                        <li class="list-group-item">
                                            <span class="badge badge-default" style="background-color: {{ array_get($calendar, 'color') }}">&nbsp;&nbsp;&nbsp;</span>
                                            {{ Form::checkbox(implode('-', ['calendar', array_get($calendar, 'id')]), array_get($calendar, 'id'), null, ['class' => 'share-calendar']) }}
                                            {{ array_get($calendar, 'name') }}
                                            @if( !array_get($calendar, 'public') )
                                                <span class="text-danger">
                                    (@lang('This calendar its setup as private'))
                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        {{ Form::hidden('display_calendar', route('calendar.share', ['id' => md5(array_get(auth()->user(), 'tenant.subdomain')), 'calendars' => '-C-']) ) }}
                        {{ Form::hidden('display_list', route('calendar.shareCalendarListMode', ['id' => md5(array_get(auth()->user(), 'tenant.subdomain')), 'calendars' => '-C-']) ) }}
                        <div class="form-group">
                            <label>@lang('URL')</label>
                            <input id="share-text" readonly type="text" class="form-control" value=""/>
                        </div>
                        <div class="form-group">
                            <label>@lang('Embed')</label>
                            <textarea id="share-iframe" readonly class="form-control" style="height: 100px;"></textarea>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="nav-export" role="tabpanel" aria-labelledby="nav-export-tab">
                        <form action="{{route('events.ics')}}" onsubmit="triggerChangeOnDateRangeThenSubmit()" method="POST" id="exportForm">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label>@lang('Select which calendars want to share')</label>
                                <div style="max-height: 300px; overflow: auto;">
                                    <ul class="list-group">
                                        @foreach($calendars as $calendar)
                                            <li class="list-group-item">
                                                <span class="badge badge-default"
                                                      style="background-color: {{ array_get($calendar, 'color') }}">&nbsp;&nbsp;&nbsp;</span>
                                                {{ Form::checkbox('calendar[]', array_get($calendar, 'id'), null, ['class' => 'share-calendar']) }}
                                                {{ array_get($calendar, 'name') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @include('_partials.human_date_range_selection',[
                                'toDateName' => 'to_date',
                                'fromDateName' => 'from_date',
                                'defaultSelectedRange' => 'This Year',
                                'prefix' => 'export_calendar'
                            ])
                            <button class="btn btn-primary" type="submit">Download</button>
                            <br>
                            <div class="mt-4">
                                You may import this <span>iCalendar (.ics)</span> file to calendar applications like Google calendar.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script type="text/javascript">
    function triggerChangeOnDateRangeThenSubmit(){
        if ($('#export_calendarhumanDateRange').val() != 'Custom') $('#export_calendarhumanDateRange').trigger('change');
    }
    (function(){
         $('.share-calendar').on('click', function(e){
             var calendars = [];
             $('.share-calendar').each(function(){
                 if($(this).prop('checked')){
                     calendars.push($(this).val());
                 }
             });
             
             var view = $('input[name="display_calendar_view"]:checked').val();
             if(view == 'calendar'){
                 var url = $('input[name="display_calendar"]').val().replace('-C-', calendars.join('-'));
             }
             else{
                 var url = $('input[name="display_list"]').val().replace('-C-', calendars.join('-'));
             }
             
             $('#share-text').val(url);
             
             var content = '<iframe src="'+url+'" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; border: none;"></iframe>';
             $('#share-iframe').val(content);
             
         });
         
         $('input[name="display_calendar_view"]').on('click',  function(){
            var view = $('input[name="display_calendar_view"]:checked').val();
            var text = $('#share-text').val();
            var textarea = $('#share-iframe').val();
            if(view == 'calendar'){
                $('#share-text').val(text.replace('/list?', '?'));
                $('#share-iframe').val(textarea.replace('/list?', '?'));
            }
            else{
                 $('#share-text').val(text.replace('/public?', '/public/list?'));
                 $('#share-iframe').val(textarea.replace('/public?', '/public/list?'));
            }
         });

         document.querySelector('#exportForm').addEventListener('submit',function (e){
             let emptyDates = Array.prototype.filter.call(document.querySelectorAll('#exportForm .datepicker'), d => d.value == '')
             let checkBoxes = document.querySelectorAll('input[type=checkbox][name="calendar[]"]')
             let checked = Array.prototype.filter.call(checkBoxes,el => el.checked);
             if (!checked.length) {
                 Swal.fire('Calendar Requred','Please select atleast 1 calendar','info');
                 e.preventDefault()
                 return false
             }
             if (emptyDates.length) {
                 Swal.fire('Date fields Required','From and To Date fields are Required','info');
                 e.preventDefault()
                 return false
             }
         })
    })();
</script>
@endpush
