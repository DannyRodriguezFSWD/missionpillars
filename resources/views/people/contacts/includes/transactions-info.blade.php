<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Transactions')</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p>&nbsp;</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('Amount')</th>
                    
                    <th>@lang('Purpose')</th>
                    <th>@lang('Fundraiser')</th>
                    <th>@lang('Time')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                    @foreach($transaction->splits as $split)
                    <tr>
                        <td>
                            <span class="badge badge-pill badge-primary">$ {{ $split->amount }}</span>
                        </td>
                        
                        <td>
                            @if($split->chart_of_account_id)
                                <small><strong>{{ $split->purpose->name }}</strong></small>
                            @endif
                        </td>
                        <td>
                            @if($split->campaign_id)
                                <small><strong>{{ $split->campaign->name }}</strong></small>
                            @endif
                        </td>
                        <td>
                            <small>{{ array_get($split, 'transaction.transaction_last_updated_at') }}</small>
                        </td>
                        
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        {{ $transactions->links() }}
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>