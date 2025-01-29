@if($registry->has('releasedTickets'))
    @foreach(array_get($registry, 'releasedTickets') as $ticket)
    <tr data-id="{{ array_get($registry, 'contact_id')}}" data-releaed="true" class="d-none">
        <td>
            {{ array_get($registry, 'contact.first_name') }}
            {{ array_get($registry, 'contact.last_name') }}
        </td>
        @if(array_get($event,'ask_whose_ticket'))
        <td></td>
        @endif
        <td><strong class="text-danger">Ticket was released</strong></td>
        <td>{{ array_get($ticket, 'ticket_name') }}</td>
        @if(array_get($event, 'is_paid') === 1)
            <td><strong class="text-danger">@lang('NO')</strong></td>
        @endif
        @if(array_get($event, 'form_id') > 1)
            <td><strong class="text-danger">@lang('NO')</strong></td>
        @endif
        <td class="text-right">
            <button class="btn btn-link p-0 text-danger" data-delete-ticket="true" data-ticket-id="{{ array_get($ticket, 'id') }}" title="Delete this record">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
    @endforeach
@else
<tr data-id="{{ array_get($registry, 'contact_id')}}">
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    @if(array_get($event, 'is_paid') === 1)
        <td><strong class="text-danger">@lang('NO')</strong></td>
    @endif
    @if(array_get($event, 'form_id') > 1)
        <td><strong class="text-danger">@lang('NO')</strong></td>
    @endif
    <td class="text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            <input class="c-switch-input checkin" type="checkbox" value="0" data-is-paid="{{ array_get($event, 'is_paid', 0) }}" data-paid="{{ array_get($registry, 'paid', 0) }}" data-form-filled="0" data-registry-id="{{ array_get($registry, 'id') }}" data-popup="#has-to-buy-tickets-modal-{{ array_get($registry, 'id') }}">
            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>
        </label>
    </td>
</tr>
@endif
@include('events.includes.checkin.modals.has-to-buy-tickets-modal')
