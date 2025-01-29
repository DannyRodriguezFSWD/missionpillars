<div class="modal fade" id="edit-calendar-widget" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    <label>@lang('Display calendar')</label>
                    <select name="calendar_id" class="form-control">
                        @foreach($calendars as $calendar)
                        <option value="{{ array_get($calendar, 'id') }}">{{ array_get($calendar, 'name') }}</option>
                        @endforeach
                    </select>
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