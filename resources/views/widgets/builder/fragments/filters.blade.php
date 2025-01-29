@php
$filters = [
'none' => 'None',
'chart_of_account' => 'Purpose',
'campaign' => 'Fundraiser',
'group' => 'Group',
'event' => 'Event',
'form' => 'Form'
]
@endphp
<div class="card-body" id="widget-filters">
    <div class="row">
        <div class="col-sm-12">
            <label>@lang('Filter by')</label>
            <div class="form-group">
                {{ Form::select('filter', $filters, null, ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="row widget-filter chart_of_account">
        <div class="col-sm-12">
            <ul class="list-group">
                <li class="list-group-item">
                    <h5 class="text-center">@lang('Select Purposes')</h5>
                </li>
                @foreach($charts as $chart)
                <li class="list-group-item">
                    {{ Form::checkbox('chart_of_account[]', array_get($chart, 'id')) }}
                    <span class="fa fa-book"></span> {{ array_get($chart, 'name') }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    
    <div class="row widget-filter campaign">
        <div class="col-sm-12">
            <ul class="list-group">
                <li class="list-group-item">
                    <h5 class="text-center">@lang('Select Fundraisers')</h5>
                </li>
                @foreach($campaigns as $campaign)
                <li class="list-group-item">
                    {{ Form::checkbox('campaign[]', array_get($campaign, 'id')) }}
                    <span class="fa fa-briefcase"></span> {{ array_get($campaign, 'name') }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    
    <div class="row widget-filter group">
        <div class="col-sm-12">
            <ul class="list-group">
                <li class="list-group-item">
                    <h5 class="text-center">@lang('Select Fundraisers')</h5>
                </li>
                @foreach($groups as $group)
                <li class="list-group-item">
                    {{ Form::checkbox('group[]', array_get($group, 'id')) }}
                    <span class="fa fa-group"></span> {{ array_get($group, 'name') }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>

</div>

@push('styles')
<style>
    .widget-filter{
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        $('select[name="filter"]').on('change', function (e) {
            $('.widget-filter').hide();
            var filter = '.widget-filter' +'.'+ $(this).val();
            $(filter).fadeIn();
        });
    })();
</script>
@endpush