<div class="modal fade" id="view-duplicate-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('View Duplicates')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
            <p class="ml-4">@lang('* Selected profile will be kept after data merge')</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="merge-duplicates">
                    <span class="fa fa-compress"></span>
                    @lang('Merge')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>