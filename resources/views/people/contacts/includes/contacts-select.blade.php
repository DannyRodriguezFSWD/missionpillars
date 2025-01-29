@foreach ($contacts as $contact)
<tr>
    <td>
        @if(array_get($contact, 'isChecked'))
            <i class="fa fa-check-square fa-lg text-success cursor-pointer no-caret" data-action="remove" onclick="syncMember(this, {{ array_get($contact, 'id') }})"></i>
        @else
            <i class="fa fa-square-o fa-lg text-primary cursor-pointer no-caret" data-action="add" onclick="syncMember(this, {{ array_get($contact, 'id') }})"></i>
        @endif
    </td>
    <td>
        <a href="{{ route('contacts.show', $contact) }}" target="_blank">{{ array_get($contact, 'first_name') }}</a>
    </td>
    <td>
        <a href="{{ route('contacts.show', $contact) }}" target="_blank">{{ array_get($contact, 'last_name') }}</a>
    </td>
    <td>{{ array_get($contact, 'email_1') }}</td>
    <td>{{ array_get($contact, 'cell_phone') }}</td>
</tr>
@endforeach
