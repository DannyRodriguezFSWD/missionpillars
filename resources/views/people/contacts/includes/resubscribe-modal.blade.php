<div class="modal fade" id="resubscribe-modal" tabindex="-1" role="dialog" aria-labelledby="resubscribeModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Contact Resubscribe')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-0">
                    <p>{{ array_get($contact, 'full_name') }} has unsubscribed from all emails.</p>
                    <p class="mb-0">Do you want to re-subscribe them? They will receive an email that lets them know that they have been re-subscribed.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="resubscribeContact();">@lang('Re-subscribe')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function resubscribeContact() {
        customAjax({
            url: '{{ route('contacts.resubscribe', array_get($contact, 'id')) }}',
            success: function (response) {
                if (response.success) {
                    Swal.fire(response.message, '', 'success');
                    window.location.reload();
                }
            }
        });
    };
</script>
@endpush
