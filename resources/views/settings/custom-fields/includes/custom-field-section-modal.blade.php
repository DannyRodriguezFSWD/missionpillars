<div class="modal fade" id="custom-field-section-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">@lang('Add New Section')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route' => ['settings.custom-fields.store-section'], 'method' => 'POST']) }}
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-form-label">@lang('Section Name') <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> @lang('Save')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#custom-field-section-modal').on('hidden.coreui.modal', function () {
        $('[name="name"]').val('');
    });
</script>
@endpush
