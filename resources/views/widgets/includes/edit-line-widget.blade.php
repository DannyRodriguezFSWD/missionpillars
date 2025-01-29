<div class="modal fade" id="edit-line-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    <input ng-model="Widget.name" type="text" class="form-control"/>
                </div>
                <div class="form-group">
                    <input name="include_last_year" ng-model="Widget.parameters['include_last_year']" value="1" type="checkbox" ng-checked="Widget.parameters.hasOwnProperty('include_last_year') ? Widget.parameters['include_last_year'] : false"/>
                    @lang('Include last year stats')
                </div>
                <div class="form-group">
                    <input name="period" ng-model="Widget.parameters['period']" value="current_year" type="radio"/> @lang('Current year')
                    <br/>
                    <input name="period" ng-model="Widget.parameters['period']" value="current_month" type="radio"/> @lang('Current month')
                    <br/>
                    <input name="period" ng-model="Widget.parameters['period']" value="date_range" type="radio"/> @lang('Date range')
                </div>
                <div class="form-group date-range" ng-show="Widget.parameters['period'] === 'date_range'">
                    <label>@lang('From')</label>
                    <input name="include_last_year" ng-model="Widget.parameters['from']" type="text" class="form-control datepicker"/>
                </div>
                <div class="form-group date-range" ng-show="Widget.parameters['period'] === 'date_range'">
                    <label>@lang('To')</label>
                    <input name="include_last_year" ng-model="Widget.parameters['to']" type="text" class="form-control datepicker"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    @lang('Close')
                </button>
                <button ng-click="Widget.actions.update()" type="button" class="btn btn-success" data-dismiss="modal">
                    @lang('Update')
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script type="text/javascript">
    /*
    (function(){
        $('input[name="period"').on('click', function(e){
            var value = $(this).val();
            value === 'date_range' ? $('.date-range').show() : $('.date-range').hide();
        });
    })();
    */
</script>
@endpush