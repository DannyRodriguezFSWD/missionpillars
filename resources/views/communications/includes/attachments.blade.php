<div class="row">
    <div class="col-6">
        <div class="card mb-0">
            <div class="card-header bg-white d-table">
                <label class="card-title mb-0 d-table-cell align-middle">
                    <i class="fa fa-paperclip"></i> Attachments
                </label>
                <label class="btn btn-light mb-0 pull-right">
                    <i class="fa fa-plus" title="Add Attachment"></i> @lang('Add Attachment')
                    <input type="file" class="d-none" onchange="addAttachmentToCommunication(this)">
                </label>
            </div>
            <div class="card-body py-2 @if($attachments->count() === 0) d-none @endif" data-attachments-container="true">
                @foreach ($attachments as $document)
                    @include ('documents.includes.document')
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addAttachmentToCommunication(button) {
        customFileUpload({
            button: $(button),
            folder: 'communication_attachments',
            relation_id: {{ array_get($communication, 'id') }},
            relation_type: '{{ addslashes(get_class($communication)) }}',
            success: function (response) {
                if (response.success) {
                    $('[data-attachments-container="true"]').removeClass('d-none').append(response.html);
                    
                    Swal.fire('Attachment added successfully', '', 'success');
                } else {
                    Swal.fire('An unexpected error occurred', 'Please try again later or contact support', 'error');
                }
                
                $(button).val(null);
            }
        });
    }
</script>
@endpush