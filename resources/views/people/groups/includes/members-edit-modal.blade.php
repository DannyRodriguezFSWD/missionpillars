<div class="modal fade" id="members-edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Manage Group Members')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                @include('people.contacts.includes.select')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function syncMember(button, contactId) {
        let action = $(button).attr('data-action');
        let groupMembersCount = parseInt($('[data-groupMembersCount]').html());
        
        customAjax({
            url: '{{ route('groups.sync-uuid', array_get($group, 'uuid')) }}',
            data: {
                contact_id: contactId,
                action: action
            },
            beforeSend: function () {
                isLoading = true;
                $(button).removeClass('fa-check-square fa-square-o text-success text-primary').addClass('fa-cog fa-spin text-warning');
            },
            success: function () {
                isLoading = false;
                $('#searchContactsDirectory').keyup();
                
                if (action === 'add') {
                    $(button).removeClass('fa-cog fa-spin text-warning').addClass('fa-check-square text-success').attr('data-action', 'remove');
                    $('[data-groupMembersCount]').html(groupMembersCount + 1);
                } else {
                    $(button).removeClass('fa-cog fa-spin text-warning').addClass('fa-square-o text-primary').attr('data-action', 'add');
                    $('[data-groupMembersCount]').html(groupMembersCount - 1);
                }
                
                
            }
        });
    }
</script>
@endpush
