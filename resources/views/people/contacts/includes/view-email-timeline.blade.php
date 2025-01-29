<div class="modal fade" id="viewEmailTimelineModal" tabindex="-1" role="dialog" aria-labelledby="viewEmailTimelineModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 m-0" style="max-height: calc(100vh - 185px); overflow-y: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    function viewTimelineEmail(id) {
        customAjax({
            url: '{{ route('contacts.timeline.view-email') }}',
            data: {
                id: id
            },
            success: function (response) {
                if (response.success) {
                    $('#viewEmailTimelineModal .modal-title').html('Subject: <b>' + response.subject + '</b>');
                    $('#viewEmailTimelineModal .modal-body').html(response.content);
                    $('#viewEmailTimelineModal').modal('show');
                } else {
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                }
            }
        });
    }
    
    function viewTimelinePrint(id) {
        customAjax({
            url: '{{ route('contacts.timeline.view-print') }}',
            data: {
                id: id
            },
            success: function (response) {
                if (response.success) {
                    $('#viewEmailTimelineModal .modal-title').html('Label: <b>' + response.label + '</b>');
                    $('#viewEmailTimelineModal .modal-body').html(response.content);
                    $('#viewEmailTimelineModal').modal('show');
                } else {
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                }
            }
        });
    }
</script>
@endpush
