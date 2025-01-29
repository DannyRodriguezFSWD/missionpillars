<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            @if( count($entries) > 0 )
                <th>@lang('form_entry_created_at')</th>
            @endif
            @foreach( $headers as $header )
                <th>
                    {{ array_get($header, 'title') }}
                </th>
            @endforeach
            @if ($export)
                <th>@lang('tags')</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($entries2 as $entry)
            <tr>
                @foreach($entry as $key => $item)
                    @if ($key !== 'tags')
                        @if(!is_numeric($key))
                            <td>{{$item}}</td>
                        @elseif(is_array($item['value']))
                            <td>{{implode(',',$item['value'])}}</td>
                        @else
                            @if($item['key'] == 'payment' || $item['key'] == 'total')
                                <td>${{ is_numeric($item['value']) ? number_format($item['value'], 2) : '' }}</td>
                            @else
                                <td>{{$item['value']}}</td>
                            @endif
                        @endif
                    @endif
                @endforeach
                @if ($export)
                    <td>{{ $entry['tags'] }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

</div>