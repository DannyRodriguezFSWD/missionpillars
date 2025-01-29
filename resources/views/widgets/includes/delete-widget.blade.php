<div class="modal fade" id="delete-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Delete Widget')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you sure you want to delete widget:') <strong class="widget-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirm-delete-widget" data-index="-1" class="btn btn-warning" data-dismiss="modal">@lang('Delete Widget')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>