<div class="modal fade" id="attendance-report-options-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-file-excel-o"></i>
                    @lang('Individual Attendance Report')
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('checkin.report', $group) }}" method="GET" id="attendanceReportOptionsForm">
                    @include('_partials.human_date_range_selection',[
                        'toDateName' => 'to_date',
                        'fromDateName' => 'from_date',
                        'defaultSelectedRange' => 'This Month',
                        'prefix' => 'attendance_report'
                    ])
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitAttendanceReport();"><i class="fa fa-file-excel-o"></i> @lang('Run Report')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function submitAttendanceReport() {
        if ($('#attendance_reporthumanDateRange').val() === 'Select Date Range' || ($('#attendance_reporthumanDateRange').val() === 'Custom' && (!$('#attendance_reportfromDate').val() || !$('#attendance_reporttoDate').val()))) {
            Swal.fire('Please select a valid date range', '', 'error');
            return false;
        }
        
        $('#attendanceReportOptionsForm').submit();
        $('#attendance-report-options-modal').modal('hide');
    }
    
</script>
@endpush
