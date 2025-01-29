@if (in_array($format, ['pdf', 'excel']))
    <div class="card-body">
        <h3>{{ array_get($givers, 'contacts_in_range_2')->count() }} {{ array_get($report, 'name') }} who have been giving between {{ $from2 }} - {{ $to2 }}</h3>
    </div>
@endif
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
            @foreach (array_get($givers, 'contacts_in_range_2', []) as $giver)
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
    
    @if (array_get($givers, 'contacts_in_range_1')->count() > 0)
    
        <table class="table">
        <tbody>
            <tr class="table-success">
                <td>&nbsp;</td>
                <td class="text-right">{{ array_get($givers, 'contacts_in_range_2')->count() }}</td>
                <td class="text-right">${{ number_format(array_get($givers, 'contacts_in_range_2')->sum('total_amount'), 2) }}</th>
                </tr>
            </tbody>
        </table>
    
    @endif
</div>