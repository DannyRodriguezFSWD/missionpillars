@foreach ($contacts as $contact)
<tr>
    <td>
        <input class="checkinPrintSelector" type="checkbox" id="checkin_print_{{ array_get($contact, 'id') }}" name="checkin_print_{{ array_get($contact, 'id') }}" data-contact-id="{{ array_get($contact, 'id') }}" checked
            data
        >
    </td>
    <td class="checkin_details_name">{{ array_get($contact, 'full_name_reverse') }}</td>
    <td class="checkin_details_parent" data-prepend="Parent">{{ array_get($contact, 'primaryContact.full_name_reverse') }}</td>
    <td class="checkin_details_grade" data-prepend="Grade">{{ array_get($contact, 'grade') }}</td>
    <td class="checkin_details_note">{{ array_get($contact, 'child_checkin_note') }}</td>
    <td class="checkin_details_phone" data-prepend="Phone">{{ array_get($contact, 'cell_phone') }}</td>
    <td class="checkin_details_parent_phone" data-prepend="Parent's phone">{{ array_get($contact, 'primaryContact.cell_phone') }}</td>
</tr>
@endforeach
