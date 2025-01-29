<div class="modal fade" id="custom-field-section-edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">@lang('Edit Section')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save-custom-field-section-edit">
                    <i class="fa fa-save"></i> @lang('Save')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                <button type="button" class="btn btn-danger" id="delete-custom-field-section">
                    <i class="fa fa-trash"></i> @lang('Delete')
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#save-custom-field-section-edit').click(function () {
        $('[name="custom-field-section-edit-form"]').submit();
    });
    
    $('#delete-custom-field-section').click(function () {
        Swal.fire({
            title: 'Are you sure you want to delete this section?',
            text: 'All fields will be moved in the Default section.',
            type: 'question',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            showLoaderOnConfirm: true
        }).then((result) => {
            if (result.value) {
                $('[name="custom-field-section-delete-form"]').submit();
            }
        });
    });
</script>
@endpush
