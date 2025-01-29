<div class="modal fade" id="addTagModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Tags</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="col-form-label">
                            Select one or more tags to add to the currently filtered contacts.
                        </label>
                        <select class="form-control" name="tag_ids" multiple style="height: 30vh" required>
                            @include('folders.model-views.tagfolders-options')
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="addTagButton" type="button" class="btn btn-primary" onclick="modalAddTags()" data-dismiss="modal">@lang('Add Tags')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script type="text/javascript">
    function modalAddTags() {
        var tag_ids = $('#addTagModal select[name=tag_ids]').val()
        // console.log('addTagCliked', tag_ids)
        if ($('#addTagModal').data('callback')) {
            // console.log('callback found',$('#addTagModal').data('callback'))
            $('#addTagModal').data('callback')(tag_ids)
        }
    }
</script>
