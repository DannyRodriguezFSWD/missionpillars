<div class="modal fade" id="widget-types" tabindex="-1" role="modal" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Add Widget')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="height: 65vh; overflow: auto;">

                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-sm-8">
                                <h6>@lang('Charts')</h6>
                                <p><small>@lang('Graphically display a variety of metrics to display as charts on your dashboard.')</small></p>
                            </div>
                            <div class="col-sm-4 text-right">
                                <button class="btn btn-success" data-toggle="modal" data-target="#widget-select-metric">
                                    <span class="fa fa-bar-chart"></span> @lang('Select')
                                </button>
                            </div>
                        </div>
                    </li>
                    @foreach($widgetTypes as $widgetType)
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-sm-8">
                                <h6>{{ __(array_get($widgetType, 'name')) }}</h6>
                                <p><small>{{ __(array_get($widgetType, 'description')) }}</small></p>
                            </div>
                            <div class="col-sm-4 text-right">
                                <button onclick="addWidget({{ array_get($widgetType, 'id') }})" class="btn btn-primary" data-id="{{ array_get($widgetType, 'id') }}" data-dismiss="modal">
                                    <span class="fa fa-check-circle-o"></span> Add
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>