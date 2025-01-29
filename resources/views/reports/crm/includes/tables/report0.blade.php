<div class="table-responsive">
    <table class="table table-striped mb-0 datatable">
        <thead>
            <tr>
                <th><i class="fa fa-sort" aria-hidden="true"></i> Name</th>
                <th class="text-center"><i class="fa fa-sort" aria-hidden="true"></i> Total Transactions</th>
                <th class="text-right"><i class="fa fa-sort" aria-hidden="true"></i> Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($givers as $giver)
            <tr>
                <td>
                    {{ array_get($giver, 'first_name') }}
                    {{ array_get($giver, 'last_name') }}
                </td>
                <td class="text-center">{{ array_get($giver, 'total_transactions') }}</td>
                <td class="text-right">${{ number_format(array_get($giver, 'total_amount'), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if ($givers->count() > 0)
        <table class="table">
        <tbody>
            <tr class="table-success">
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="text-right">{{ $givers->sum('total_transactions') }}</td>
                <td class="text-right">${{ number_format($givers->sum('total_amount'), 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>