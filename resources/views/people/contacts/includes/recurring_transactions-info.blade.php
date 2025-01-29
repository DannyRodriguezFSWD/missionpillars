<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Recurring Transactions')</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p>&nbsp;</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>@lang('Amount')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Purpose')</th>
                    <th>@lang('Fundraiser')</th>
                    <th>@lang('Billing Cycles')</th>
                    <th>@lang('Billing Frequency')</th>
                    <th>@lang('Successes')</th>
                    <th>@lang('Remaining')</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recurring as $template)
                    @foreach($template->splits as $split)
                        <tr class="clickable-row" data-href="{{ route('recurring.show', ['id' => array_get($split, 'id')]) }}">
                            <td>
                                <span class="badge badge-pill badge-primary">$ {{ $split->amount }}</span>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'type') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'purpose.name') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'campaign.name', 'None') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($template, 'billing_cycles') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($template, 'billing_frequency') }} {{ array_get($template, 'billing_frequency', 0) > 1 ? array_get($template, 'billing_period').'s' : array_get($template, 'billing_period') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'template.successes', 0) }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($template, 'billing_cycles', 0) - array_get($split, 'template.successes', 0) }}</small>
                            </td>
                            <td><span class="icon icon-arrow-right"></span></td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>