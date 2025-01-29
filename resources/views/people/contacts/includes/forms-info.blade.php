
<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Submited Forms')</p>
        <!--
        <div class="btn-group float-right">
            <a class="btn btn-primary" href="{{ route('addresses.create', ['id' => $contact->id]) }}">
                <i class="icon icon-location-pin"></i> 
                Add Address
            </a>
        </div>
        -->
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p>&nbsp;</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('Form')</th>
                    <th>@lang('Accept payments')?</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Status')</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                @php
                    $e = json_decode(array_get($entry, 'json', '{}'), true);
                @endphp
                <tr class="clickable-row" data-href="{{ route('entries.show', ['id' => array_get($entry, 'id')]) }}">
                    <td>{{ array_get($entry, 'form.name') }}</td>
                    <td>
                        @if (array_get($entry, 'form.accept_payments'))
                            <span class="text-success">YES</span>
                        @else
                            <span class="text-primary">NO</span>
                        @endif
                    </td>
                    <td>{{ date('M d, Y g:i:s A', strtotime($entry->created_at)) }}</td>
                    @if (array_get($entry, 'form.accept_payments'))
                        <td>
                            @if(!is_null(array_get($entry, 'transaction_id')))
                                <span class="text-success">${{ number_format(array_get($entry, 'transaction.splits.0.amount', 0), 2) }}</span>
                            @else
                                <span class="text-danger">${{ number_format(array_get($e, 'total', 0), 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(array_get($entry, 'transaction.status') == 'complete')
                                <span class="text-success">@lang('Paid')</span>
                            @elseif(array_get($entry, 'transaction.status') == 'pending')
                                <span class="text-success">@lang('Pending')</span>
                            @else
                                <span class="text-danger">@lang('Not Paid')</span>
                            @endif
                        </td>
                    @else
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    @endif
                    
                    
                    <td class="text-right">
                        <span class="icon icon-arrow-right"></span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>