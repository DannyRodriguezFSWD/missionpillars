<div id="templatePreviewModal" class="modal fade" tabindex="-1" role="dialog" style="z-index: 2000;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <div class="w-100">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-primary" id="selectSecreenMobile">
                            <input type="radio" name="screen" id="screenMobile" autocomplete="off"> <i class="fa fa-mobile"></i> @lang('Mobile')
                        </label>
                        <label class="btn btn-outline-primary active" id="selectSecreenDesktop">
                            <input type="radio" name="screen" id="screenDesktop" autocomplete="off" checked> <i class="fa fa-desktop"></i> @lang('Desktop')
                        </label>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="selectTemplate">@lang('Use this template')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>
