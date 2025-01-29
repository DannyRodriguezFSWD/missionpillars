<div class="modal fade" id="share-group-modal-{{ $group->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Share this link so people can sign up')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-globe"></i></span>
                    </div>
                    <input type="text" id="group_public_link_{{ $group->id }}" class="form-control" readonly="true" value="{{ route('join.show', ['id' => $group->uuid]) }}"/>
                    <div class="input-group-append">
                        <button class="btn btn-outline-info" type="button" onclick="copy('group_public_link_{{ $group->id }}')">
                            <i class="fa fa-copy"></i> Copy
                        </button>
                    </div>
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
