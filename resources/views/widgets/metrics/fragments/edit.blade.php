<div class="modal fade" id="edit-chart-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Edit Widget')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('Widget title')</label>
                    <input name="name" type="text" class="form-control widget-name"/>
                </div>
                
                <div class="form-group show-last-year">
                    <input name="include_last_year" value="1" type="checkbox"/>
                    @lang('Include last year stats')
                </div>
                <div class="form-group">
                    <label><strong>@lang('Metric')</strong></label>
                    <div class="row">
                        <div class="col-sm-4 widget-parameters-name"></div>
                        <div class="col-sm-5 widget-parameters-description"></div>
                        <div class="col-sm-3 text-right">
                            <button onclick="getMetrics(this)" data-index="-1" class="btn btn-success" data-toggle="modal" data-target="#select-metric-widget">
                                @lang('Select Metric')
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <input name="period" value="current_year" type="radio"/> @lang('Current year')
                    <br/>
                    <input name="period" value="current_month" type="radio"/> @lang('Current month')
                    <br/>
                    <input name="period" value="date_range" type="radio"/> @lang('Date range')
                </div>
                <div class="form-group date-range">
                    <label>@lang('From')</label>
                    <input name="from" type="text" class="form-control datepicker"/>
                </div>
                <div class="form-group date-range">
                    <label>@lang('To')</label>
                    <input name="to" type="text" class="form-control datepicker"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    @lang('Close')
                </button>
                <button onclick="updateWidget(this)" data-index="-1" type="button" class="btn btn-success" data-dismiss="modal">
                    @lang('Update')
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('styles')
<style>
    div.form-group.date-range{
        display: none;
    }
</style>
@endpush
@push('scripts')
<script type="text/javascript">
    
    (function(){
        $('input[name="period"').on('click', function(e){
            var value = $(this).val();
            value === 'date_range' ? $('.date-range').show() : $('.date-range').hide();
        });
    })();
    
</script>
@endpush