<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Remove Transaction')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you sure you want to remove transaction?')</p>
                <small>@lang("This action can't be undone")</small>
            </div>
            <div class="modal-footer">
                <button id="button-delete" type="button" class="btn btn-warning" data-dismiss="modal">@lang('Remove Transaction')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    var currentForm;
    $('.delete').on('click', function (e) {
        currentForm = $(this).data('form');
        console.log(currentForm);
    });

    $('#button-delete').on('click', function (e) {
        $(currentForm).submit();
    });
</script>
@endpush
