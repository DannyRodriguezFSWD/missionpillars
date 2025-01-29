@foreach(array_get($registry, 'tickets') as $ticket)
<tr>
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    <td>{{ array_get($registry, 'contact.email_1') }}</td>
    <td>{{ array_get($registry, 'event.template.name') }}</td>
    <td>{{ array_get($registry, 'event.template.start') }}</td>
    <td>{{ array_get($registry, 'event.template.end') }}</td>
    <td>{{ array_get($ticket, 'id') }}</td>
    <td>{{ array_get($ticket, 'ticket_name') }}</td>
    <td>{{ array_get($ticket, 'price') }}</td>
    <td>
        @if(array_get($registry, 'paid'))
            @lang('YES')
        @else
            @lang('NO')
        @endif
    </td>
    <td>{{ array_get($registry, 'transaction.transaction_initiated_at') }}</td>
    @if($whose_ticket)
        <td>{{ $ticket->first_name }} {{ $ticket->last_name }}</td>
        <td>{{ $ticket->email }}</td>
    @endif
    <td>
        @if(array_get($ticket, 'form_filled'))
            @lang('YES')
        @else
            @lang('NO')
        @endif
    </td>
    <td>
        @if( array_get($ticket, 'checked_in') )
            @lang('YES')
        @else
            @lang('NO')
        @endif
    </td>
    @foreach($allFormsNamesAndLabels as $name => $label)
        <td>
        @if($ticket->formEntry)
            @foreach($allFormsNamesAndLabelsFlipped[$label] as $val)
                @isset($ticket->formEntry->jsonValues[$val])
                    @if(is_array($ticket->formEntry->jsonValues[$val]))
                        {{ implode(', ', $ticket->formEntry->jsonValues[$val]) }}
                    @else
                        {{ $ticket->formEntry->jsonValues[$val] }}
                    @endif
                    @break
                @endisset
            @endforeach
        @endif
        </td>
    @endforeach
    <td>{{ $ticket->extras }}</td>
</tr>
@endforeach