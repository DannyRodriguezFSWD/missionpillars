@foreach(array_get($registry, 'tickets') as $ticket)
<tr data-id="{{ array_get($registry, 'contact_id')}}">
    <td>
        {{ array_get($registry, 'contact.first_name') }}
        {{ array_get($registry, 'contact.last_name') }}
    </td>
    @if(array_get($event,'ask_whose_ticket'))
        <td>
            @if($ticket->first_name || $ticket->last_name || $ticket->email)
                <table class="table table-sm table-bordered">
                    <tbody>
                    @if($ticket->first_name || $ticket->last_name)<tr><td><b>Ticket For:&nbsp;</b>{{$ticket->first_name}}&nbsp;{{$ticket->last_name}}</td></tr>@endif
                    @if($ticket->email)<tr><td><b>Email:&nbsp;</b>{{$ticket->email}}</td></tr>@endif
                    </tbody>
                </table>
            @endif
        </td>
    @endif
    <td>{{ array_get($ticket, 'id') }}</td>
    <td>{{ array_get($ticket, 'ticket_name') }}</td>
    @if($show_paid_column)
    <td>
        @if(array_get($ticket, 'registry.paid'))
        <a href="{{ route('transactions.show', ['id' => array_get($registry, 'transaction.splits.0.id')]) }}" class="btn btn-secondary" target="_blank">
            <strong class="text-success">${{ $ticket->price }}</strong>
            <i class="fa fa-info-circle text-info"></i>
        </a>
        @else
        <strong class="text-danger">${{ $ticket->price }}</strong>
        @endif
    </td>
    @endif
    @if(array_get($event, 'form_id') > 1)
    <td>
        @if(array_get($ticket, 'form_filled'))
        <a href="{{ route('contacts.form', ['id' => array_get($registry, 'contact.id'), 'entry' => array_get($ticket, 'form_entry_id')]) }}" class="btn btn-secondary" target="_blank">
            <strong class="text-success">@lang('YES')</strong>
            <i class="fa fa-info-circle text-info"></i>
        </a>
        @else
        <strong class="text-danger">@lang('NO')</strong>
        @endif
    </td>
    @endif
    <td class="text-right">
        <label class="c-switch c-switch-label  c-switch-primary">
            @if( array_get($ticket, 'checked_in') )
            <input checked="" class="c-switch-input checkin" type="checkbox" value="{{ array_get($ticket, 'id', 0) }}" data-is-paid="{{ array_get($registry, 'event.template.is_paid', 0) }}" data-paid="{{ array_get($registry, 'paid', 0) }}" data-form-filled="{{ array_get($ticket, 'form_filled') }}" data-popup="#has-to-pay-modal-{{ array_get($ticket, 'id') }}" data-form="#has-to-fill-form-modal-{{ array_get($ticket, 'id') }}">
            @else
            <input class="c-switch-input checkin" type="checkbox" value="{{ array_get($ticket, 'id', 0) }}" data-is-paid="{{ array_get($registry, 'event.template.is_paid', 0) }}" data-paid="{{ array_get($registry, 'paid', 0) }}" data-form-filled="{{ array_get($ticket, 'form_filled') }}" data-popup="#has-to-pay-modal-{{ array_get($ticket, 'id') }}" data-form="#has-to-fill-form-modal-{{ array_get($ticket, 'id') }}">
            @endif
            <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

        </label>
    </td>
</tr>
@include('events.includes.checkin.modals.has-to-pay-modal')
@include('events.includes.checkin.modals.has-to-fill-form-modal')
@endforeach