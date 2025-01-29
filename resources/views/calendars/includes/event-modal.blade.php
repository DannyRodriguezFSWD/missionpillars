<div class="modal fade" id="event-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-calendar"></i>
                    <span></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <p class="text-uppercase">
                            <i class="fa fa-clock-o"></i>
                            <span class="date"></span>
                        </p>
                        <p class="text-uppercase info-reserve-tickets" style="display: none;">
                            <i class="fa fa-info-circle text-primary"></i>
                            @lang('This event requires ticket reservation')
                        </p>
                        <div class="text-uppercase address">
                            <i class="fa fa-home"></i>
                            <span></span>
                        </div>
                        <p>
                            <br>
                            <small class="text-uppercase description">
                                <i class="fa fa-comment"></i>
                                <span></span>
                            </small>
                        </p>
                    </div>
                    <div class="col-sm-6 text-uppercase">
                        <div class="tickets">
                            <p><i class="fa fa-ticket"></i> Ticket Options</p>
                            <div style="max-height: 225px; overflow: auto; border: 1px solid rgba(0, 0, 0, 0.125)">
                                <ul class="list-group"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="url" value="{{ route('events.share', ['id' => ':ID:']) }}"/>
                <button type="button" class="btn btn-link" data-dismiss="modal">@lang('Close')</button>
                @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => '', 'caption' => 'Register now', 'form' => true])
                <!--
                <a class="btn btn-lg btn-success" href="">
                    @lang('Register now')
                </a>
                -->
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>