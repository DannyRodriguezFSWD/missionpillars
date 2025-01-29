<div class="modal fade" id="removeTagModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Remove Tags</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <form>
                    <div class="form-group">
                        <label class="col-form-label">
                            Select one or more tags to remove from the currently filtered contacts.
                        </label>
                        <select class="" name="tag_ids" multiple style="height: 30vh" required>
                            @include('folders.model-views.tagfolders-options')
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="remove" type="button" class="btn btn-primary" onclick="modalRemoveTags()" data-dismiss="modal">@lang('Remove Tags')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script type="text/javascript">
    function modalRemoveTags() {
        var tag_ids = $('#removeTagModal select[name=tag_ids]').val()
        // console.log('removeTagCliked', tag_ids)
        if ($('#removeTagModal').data('callback')) {
            // console.log('callback found',$('#removeTagModal').data('callback'))
            $('#removeTagModal').data('callback')(tag_ids)
        }
    }
</script>
