<div class="modal fade" id="delete-address-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Delete Address')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you sure you want to delete selected address?')</p>
                <small>@lang("This action can't be undone")</small>
            </div>
            <div class="modal-footer">
                <button id="button-delete" type="button" class="btn btn-warning" data-dismiss="modal">@lang('Delete Address')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script type="text/javascript">
    var currentForm;


    $(function () {
        $('#delete-address-modal').on('shown.coreui.modal', function (e) {
            var button = $(e.relatedTarget);
            currentForm = document.getElementById(button.data('id'));
        });

        $('#button-delete').on('click', function (e) {
            $(currentForm).submit();
        });
    });
</script>
@endpush