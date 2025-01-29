<div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span class="fa fa-share-alt"></span>
                    @lang('Share Calendar')
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('URL')</label>
                    <input disabled="" type="text" class="form-control" value="{{ route('calendar.share', ['id' => array_get($calendar, 'uuid')]) }}"/>
                </div>
                <div class="form-group">
                    <label>@lang('Embed')</label>
                    <textarea disabled="" class="form-control"><iframe src="{{ route('calendar.share', ['id' => array_get($calendar, 'uuid')]) }}" style="width: 100%!important; height: 800px;"></iframe></textarea>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>