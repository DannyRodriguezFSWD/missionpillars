<div class="modal fade" id="edit-kpis-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    <input name="name" type="text" class="form-control"/>
                </div>
                
                <div class="form-group">
                    <label>@lang('Year')</label>
                    <select name="period" class="form-control">
                        @foreach($years as $year)
                        @if($loop->first)
                            <option selected="" value="{{ $year }}">{{ $year }}</option>
                        @else
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <div>
                        <input type="checkbox" name="average_annual_giving_donor" value="true"/> @lang('Average Annual Giving/Donor')
                    </div>
                    <div>
                        <input type="checkbox" name="average_gift" value="true"/> @lang('Average Gift')
                    </div>
                    <div>
                        <input type="checkbox" name="donors_in_database" value="true"/> @lang('Donors in Database')
                    </div>
                    <div>
                        <input type="checkbox" name="donors_retention_rate" value="true"/> @lang('Donor Retention Rate')
                    </div>
                    <div>
                        <input type="checkbox" name="donor_attrition_rate" value="true"/> @lang('Donor Attrition Rate')
                    </div>
                    <div>
                        <input type="checkbox" name="donor_participation_rate" value="true"/> @lang('Donor Participation Rate')
                    </div>
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