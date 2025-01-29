<div class="modal fade" id="family-info-modal" tabindex="-1" role="dialog" aria-labelledby="familyInfoModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Family') - <span data-family-name="true"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    function showFamilyInfo(familyId) {
        customAjax({
            url: '{{ route('families.info') }}',
            data: {
                family_id: familyId
            },
            success: function (response) {
                $('#family-info-modal [data-family-name="true"]').html(response.family_name);
                $('#family-info-modal .modal-body').html(response.html);
                $('#family-info-modal').modal('show');
            }
        });
    }
</script>
@endpush