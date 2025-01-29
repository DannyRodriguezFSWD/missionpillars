<div class="modal fade" id="select-metric-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Select Chart')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="height: 65vh; overflow: auto;">
                <div class="form-group">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-sm-8">
                                    <h6><% metric.name %></h6>
                                    <p><small><% metric.description %></small></p>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <button onclick="changeWidget(this)" data-index="-1" class="btn btn-primary" data-dismiss="modal">
                                        <span class="fa fa-check-circle-o"></span> Add
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    @lang('Cancel')
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

