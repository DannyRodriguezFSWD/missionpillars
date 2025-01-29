<div class="modal fade" id="alert-change-recursion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Change Schedule')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Warning: Changing the recurring schedule will adjust the dates for this event and all future events in this series. Check in data and volunteer assignments currently associated with this event and future events in this series will be lost (data in previous events in this series will be retained). Are you sure you want to continue?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('No')</button>
                <button type="button" id="btn-schedule-yes" class="btn btn-warning" data-dismiss="modal">@lang('Yes')</button>
            </div>
            
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


