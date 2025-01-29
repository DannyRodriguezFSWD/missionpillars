<div class="form-group">
    <label for="humanDateRange">Date Range:</label>
    <select name="dateRange" class="form-control" id="{{$prefix}}humanDateRange">
        <option value="Select Date Range">Select Date Range</option>
        <option value="Custom">Custom</option>
        <option value="Today">Today</option>
        <option value="Yesterday">Yesterday</option>
        <option value="This Week">This Week</option>
        <option value="Last Week">Last Week</option>
        <option value="This Month">This Month</option>
        <option value="Last Month">Last Month</option>
        <option value="This Year">This Year</option>
        <option value="Last Year">Last Year</option>
    </select>
    <div class="row mt-2 {{$prefix}}fromToDateDiv @if(empty($fromDateVal) || empty($toDateVal)) d-none @endif">
        <div class="col-md-6">
            <div class="form-group">
                <label for="">From:</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                    <input type="text" placeholder="Choose Date" autocomplete="off" id="{{$prefix}}fromDate" value="{{!empty($fromDateVal) ? $fromDateVal : ''}}" name="{{$fromDateName}}" class="form-control datepicker">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="">To:</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                    <input type="text" placeholder="Choose Date" autocomplete="off" id="{{$prefix}}toDate" value="{{!empty($toDateVal) ? $toDateVal : ''}}" name="{{$toDateName}}" class="form-control datepicker">
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        (function(){
            const humanDateRangeId = '#{{$prefix}}humanDateRange'
            const defaultSelectedRange = '{{$defaultSelectedRange}}'
            const toDateEl = $('#{{$prefix}}toDate')
            const fromDateEl = $('#{{$prefix}}fromDate')
            const humanDateRangeEl = $(humanDateRangeId);
            const fromToDateDiv = $('.{{$prefix}}fromToDateDiv');

            if (document.querySelector(humanDateRangeId).form){
                let old_reset = document.querySelector(humanDateRangeId).form.reset;
                document.querySelector(humanDateRangeId).form.reset = function (){
                    old_reset.apply(this,arguments)
                    humanDateRangeEl.val(defaultSelectedRange);
                    humanDateRangeEl.trigger('change')
                }
            }

            humanDateRangeEl.on('change', function () {
                $(this).val() == 'Custom' ? fromToDateDiv.removeClass('d-none') : fromToDateDiv.addClass('d-none')
                switch ($(this).val()) {
                    case "Today":
                        fromDateEl.datepicker("setDate", humanDateRange_today);
                        toDateEl.datepicker("setDate", humanDateRange_today);
                        break;
                    case "Yesterday":
                        fromDateEl.datepicker("setDate", humanDateRange_yesterday);
                        toDateEl.datepicker("setDate", humanDateRange_yesterday);
                        break;
                    case "Last Week":
                        fromDateEl.datepicker("setDate", humanDateRange_last_week_start);
                        toDateEl.datepicker("setDate", humanDateRange_last_week_end);
                        break;
                    case "This Week":
                        fromDateEl.datepicker("setDate", humanDateRange_this_week_start);
                        toDateEl.datepicker("setDate", humanDateRange_this_week_end);
                        break;
                    case "Last Month":
                        fromDateEl.datepicker("setDate", humanDateRange_last_month_start);
                        toDateEl.datepicker("setDate", humanDateRange_last_month_end);
                        break;
                    case "This Month":
                        fromDateEl.datepicker("setDate", humanDateRange_this_month_start);
                        toDateEl.datepicker("setDate", humanDateRange_this_month_end);
                        break;
                    case "Last Year":
                        fromDateEl.datepicker("setDate", humanDateRange_last_year_start);
                        toDateEl.datepicker("setDate", humanDateRange_last_year_end);
                        break;
                    case "This Year":
                        fromDateEl.datepicker("setDate", humanDateRange_this_year_start);
                        toDateEl.datepicker("setDate", humanDateRange_this_year_end);
                        break;
                    case "Custom":
                        fromDateEl.datepicker("setDate", "");
                        toDateEl.datepicker("setDate", "");
                        break;
                    case "Select Date Range":
                        fromDateEl.datepicker("setDate", "");
                        toDateEl.datepicker("setDate", "");
                        break;
                }
            })

            @if(!empty($fromDateVal) || !empty($toDateVal))
            humanDateRangeEl.val('Custom')
            @elseif(!empty($defaultSelectedRange))
            humanDateRangeEl.val(defaultSelectedRange)
            humanDateRangeEl.trigger('change')
            @endif
        })()
    </script>
@endpush