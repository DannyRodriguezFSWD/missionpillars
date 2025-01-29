<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            @if( count($entries) > 0 )
            <th>@lang('form_entry_created_at')</th>
            @endif
            @foreach( $headers as $header )
            <th>{{ $header }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach( $entries as $entry )
        @php
        $row = json_decode(array_get($entry, 'json'), true);
        @endphp
        @if( count($row) > 0 )
        <tr>
            <td>
                {{ displayLocalDateTime(array_get($entry, 'created_at'))->toDayDateTimeString() }}
            </td>
            @foreach($row as $key => $value)
            @if( $key == 'search' )
            @continue
            @endif
            @if( is_array($value) )
            @foreach($value as $item)
            <td>
                {{ $item }}
            </td>
            @endforeach
            @else
            <td>
                {{ $key }}
                {{ json_encode($value) }}
            </td>
            @endif
            @endforeach
        </tr>
        @endif
        @endforeach
        </tbody>
    </table>
</div>
