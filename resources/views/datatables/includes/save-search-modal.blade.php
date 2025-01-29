<div class="modal fade" id="saveSearchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Save Search</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="col-form-label">
                            What should we name your current search filter?
                        </label>
                        <input class="form-control" name="name" minlength="4" required onchange="checkStateName()" onkeypress="checkStateName()">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="saveSearchButton" type="button" class="btn btn-primary" onclick="modalSaveSearch()" data-dismiss="modal" disabled>@lang('Save Search')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script type="text/javascript">
    function modalSaveSearch() {
        var name = $('#saveSearchModal input[name=name]').val()
        if ($('#saveSearchModal').data('callback')) {
            $('#saveSearchModal').data('callback')(name)
        }
        $('#saveSearchModal form').get(0).reset()
    }

    function checkStateName() {
        $('#saveSearchModal #saveSearchButton').attr( 'disabled',
            ! $('#saveSearchModal form').get(0).checkValidity() )
    }

</script>

<style media="screen">
    #saveSearchModal input {
        width: 100%;
    }
    #saveSearchModal .modal-content {
        font-size: larger;
    }
</style>
