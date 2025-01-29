<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #74787e;">
    <tr>
        <td>
            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <p>Dear {{ array_get($manager, 'first_name') }}</p>
                        <p>You have a new signup!</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <strong>Who:</strong>
                        {{ array_get($contact, 'first_name') }}
                        {{ array_get($contact, 'last_name') }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Event:</strong>
                        {{ array_get($event, 'template.name') }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>When:</strong>
                        @if( array_get($event, 'template.is_all_day') )
                        {{ displayLocalDateTime(array_get($event, 'start_date'), array_get($event, 'template.timezone'))->toDateString() }}
                        @else
                        {{ displayLocalDateTime(array_get($event, 'start_date'), array_get($event, 'template.timezone'))->toDayDateTimeString() }}
                        -
                        {{ displayLocalDateTime(array_get($event, 'end_date'), array_get($event, 'template.timezone'))->toDayDateTimeString() }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Where:</strong>
                        {{ array_get($event, 'template.addressInstance.0.mailing_address_1') }}<br>
                        {{ array_get($event, 'template.addressInstance.0.region') }}<br>
                        {{ array_get($event, 'template.addressInstance.0.city') }}<br>
                        {{ array_get($event, 'template.addressInstance.0.countries.name') }}<br>
                    </td>
                </tr>
                @if(array_get($event, 'template.allow_reserve_tickets') && !is_null($tickets_summary) && count($tickets_summary) > 0)
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <tr>
                                <td><strong>Ticket type</strong></td>
                                <td><strong>Price</strong></td>
                                <td><strong>Quantity</strong></td>
                                <td><strong>Subtotal</strong></td>
                            </tr>
                            @foreach($tickets_summary as $ticket)
                            <tr>
                                <td>{{ array_get($ticket, 'ticket_name') }}</td>
                                <td>${{ number_format(array_get($ticket, 'price', 0), 2) }}</td>
                                <td>{{ array_get($ticket, 'tickets') }}</td>
                                <td>${{ number_format(array_get($ticket, 'subtotal', 0), 2) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="border-top: 1px #000 solid;">&nbsp;</td>
                                <td style="border-top: 1px #000 solid;">&nbsp;</td>
                                <td style="border-top: 1px #000 solid;"><strong>Total</strong></td>
                                <td style="border-top: 1px #000 solid;">${{ number_format($total, 2) }}</td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <p>You can manage this event from your admin panel here <a href="{{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}crm/events/{{ array_get($event, 'id') }}/checkin">{{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}events/{{ array_get($event, 'id') }}/checkin</a>.</p>
                        <p>A receipt with a qr code has been emailed to {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}.  You can scan this qr code at the door to check them in and confirm the ticket is valid and only used on time.</p>
                        <p>You will also be able to check them in from the event management screen if they do not have their ticket.</p>
                        <p>
                            Sincerely<br>The Mission Pillars Team
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
