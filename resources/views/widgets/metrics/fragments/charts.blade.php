<div class="modal fade" id="widget-select-metric" tabindex="-1" role="modal" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Select Chart add.metric')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="height: 65vh; overflow: auto;">
                <ul class="list-group">
                    @foreach($metrics as $metric)
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-sm-8">
                                <h6>{{ __(array_get($metric, 'name')) }}</h6>
                                <p><small>{{ __(array_get($metric, 'description')) }}</small></p>
                            </div>
                            <div class="col-sm-4 text-right">
                                <button onclick="addMetric({{ array_get($metric, 'id') }})" class="btn btn-primary" data-id="{{ array_get($metric, 'id') }}" data-dismiss="modal">
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