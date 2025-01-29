<tr id="{{ array_get($registry, 'id')}}">
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>

    <td class="text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            <input class="c-switch-input checkin" name="cid" type="checkbox" value="{{ array_get($registry, 'id') }}" data-form_filled="">
            <span class="c-switch-slider" data-checked="" data-unchecked=""></span>

        </label>
    </td>
</tr>