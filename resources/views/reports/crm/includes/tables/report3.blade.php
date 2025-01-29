{{-- Excel doesn't like cell widths --}}
@if (request('format') != "excel")
    <style>
    .purposes_total_column {
        width: 68px;
    }
    </style>
@endif
<div class="table-responsive">
    <table class="table table-striped mb-0 datatable">
        <thead>
            <tr>
                @foreach ($givers as $giver)
                    @if($loop->first)
                        <th><i class="fa fa-sort" aria-hidden="true"></i> Purpose</th>
                        @foreach(array_get($giver, 'ranges', []) as $range)
                            <th class="text-center h6" style="white-space:nowrap"><i class="fa fa-sort" style="border-left: solid lightgrey 1px" aria-hidden="true"></i> {{ array_get($range, '0.value') }}</th>
                            <th class="text-right h6" style="{{ $loop->last ?' white-space:nowrap;':'' }}"><i class="fa fa-sort" aria-hidden="true"></i> {{ array_get($range, '1.value') }}</th>
                        @endforeach
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(count($givers) <= 0)
            <tr>
                <td>&nbsp;</td>
                <td class="text-right">&nbsp;</td>
                <td class="text-right">&nbsp;</td>
            </tr>
            @endif
            @foreach ($givers as $giver)
            <tr>
                <td style="white-space: nowrap">
                    {{ array_get($giver, 'name') }}
                </td>
                @foreach(array_get($giver, 'ranges', []) as $range)
                    <td class="text-right" style="border-left: solid lightgrey 1px; min-width: {{ $loop->last ? '68px' : '4ch' }}">{{ array_get($range, '0.total_transactions') }}</td>
                    <td class="text-right" style="min-width: '68px';{{ $loop->last ?'':'max-width: 15%;' }}">${{ number_format(array_get($range, '1.total_amount'), 2) }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if (!empty($sum))
        <table class="table">
            <tbody>
                <tr class="table-success">
                    <td>&nbsp;</td>
                    @foreach(array_get($sum, 'ranges', []) as $range)
                        @if(count($range) > 0)
                        <td class="text-right purposes_total_column">{{ array_get($range, '0.total_transactions') }}</td>
                        <td class="text-right purposes_total_column">${{ number_format(array_get($range, '1.total_amount'), 2) }}</td>
                        @else
                        <td class="text-center">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    @endif
</div>
