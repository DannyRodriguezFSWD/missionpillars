@if($registry->has('releasedTickets'))
<tr>
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    <td>{{ array_get($registry, 'contact.email_1') }}</td>
    <td>{{ array_get($registry, 'event.template.name') }}</td>
    <td>{{ array_get($registry, 'event.template.start') }}</td>
    <td>{{ array_get($registry, 'event.template.end') }}</td>
    <td>Ticket was released</td>
    <td></td>
    <td></td>
    <td>
        @if(array_get($registry, 'paid'))
            @lang('YES')
        @else
            @lang('NO')
        @endif
    </td>
    <td></td>
    @if($whose_ticket)
        <td></td>
        <td></td>
    @endif
    <td>@lang('NO')</td>
    <td>@lang('NO')</td>
    @foreach($allFormsNamesAndLabels as $name => $label)
        <td></td>
    @endforeach
    <td></td>
</tr>
@else
<tr>
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    <td>{{ array_get($registry, 'contact.email_1') }}</td>
    <td>{{ array_get($registry, 'event.template.name') }}</td>
    <td>{{ array_get($registry, 'event.template.start') }}</td>
    <td>{{ array_get($registry, 'event.template.end') }}</td>
    <td></td>
    <td></td>
    <td></td>
    <td>
        @if(array_get($registry, 'paid'))
            @lang('YES')
        @else
            @lang('NO')
        @endif
    </td>
    <td></td>
    @if($whose_ticket)
        <td></td>
        <td></td>
    @endif
    <td>@lang('NO')</td>
    <td>@lang('NO')</td>
    @foreach($allFormsNamesAndLabels as $name => $label)
        <td></td>
    @endforeach
    <td></td>
</tr>
@endif