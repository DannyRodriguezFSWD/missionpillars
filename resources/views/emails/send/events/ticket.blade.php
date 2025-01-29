<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0">

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">
                        
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <tr>
                                <td colspan="2" style="padding-bottom: 35px">
                                    {!! $split->template->custom_header !!}
                                </td>
                            </tr>
                            <tr style="padding: 35px 0px">
                                <td>
                                    <h3>{{ array_get($tenant, 'organization') }} - @lang('Event Ticket')</h3>
                                </td>
                                <td style="text-align: right">
                                    <h3>{{ humanReadableDate(date('Y-m-d')) }}</h3>
                                </td>
                            </tr>
                        </table>
                        
                        
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            <tr>
                                <td>
                                    <p>Dear {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</p>
                                    <p>Thank you very much for signing up for {{ array_get($split, 'template.name') }}.  Please use this as your ticket for the event.</p>
                                </td>
                            </tr>
                        </table>
                        
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            @if(!is_null($transaction))
                            <tr>
                                <td>
                                    <strong>@lang('Transaction'): </strong>
                                    {{ array_get($transaction->getAltIds()->first(), 'alt_id') }}
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <strong>@lang('Name'): </strong>
                                    {{ array_get($contact, 'first_name') }}
                                    {{ array_get($contact, 'last_name') }}
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <strong>@lang('Email'): </strong>
                                    {{ array_get($contact, 'email_1') }}
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <strong>@lang('Settlement'): </strong>
                                    {{ array_get($transaction, 'status') }}
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <strong>@lang('Credit/Debit Card'): </strong>
                                    {{ array_get($transaction, 'paymentOption.card_type') }}
                                    **** {{ array_get($transaction, 'paymentOption.last_four') }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td >
                                    <strong>@lang('Event'): </strong>
                                    {{ array_get($split, 'template.name') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>@lang('When'): </strong>
                                    @if( array_get($split, 'template.is_all_day') )
                                    {{ displayLocalDateTime(array_get($split, 'start_date'), array_get($split, 'template.timezone'))->toDateString() }}
                                    @else
                                    {{ displayLocalDateTime(array_get($split, 'start_date'), array_get($split, 'template.timezone'))->toDayDateTimeString() }}
                                    -
                                    {{ displayLocalDateTime(array_get($split, 'end_date'), array_get($split, 'template.timezone'))->toDayDateTimeString() }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <strong>Where:</strong>
                                    {{ array_get($split, 'template.addressInstance.0.mailing_address_1') }}<br>
                                    {{ array_get($split, 'template.addressInstance.0.region') }}<br>
                                    {{ array_get($split, 'template.addressInstance.0.city') }}<br>
                                    {{ array_get($split, 'template.addressInstance.0.countries.name') }}<br>
                                </td>
                            </tr>
                        </table>
                        <br>
                        @if(array_get($split, 'template.allow_reserve_tickets'))
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
                        <br>
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            <tr>
                                <td>
                                    <p>Below is a one time use ticket.  If you scan this, it will be marked as used.</p>
                                </td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                        @endif
                    </td>
                </tr>
            </table>
            
            @if(array_get($split, 'template.allow_reserve_tickets'))
            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                @foreach($tickets as $ticket)
                <tr>
                    <td style="border-top: 1px #000 dashed; padding: 20px;">
                        <p>
                            <strong>@lang('Ticket Number'): </strong>
                            {{ array_get($ticket, 'id') }}
                        </p>
                        <p>
                            <strong>@lang('Ticket Type'): </strong>
                            {{ array_get($ticket, 'ticket_name') }}
                        </p>
                        <p>
                            <strong>@lang('Ticket ID'): </strong>
                            {{ array_get($ticket, 'uuid') }}
                        </p>
                        @if (array_get($split, 'template.ask_whose_ticket') && (array_get($ticket, 'first_name') || array_get($ticket, 'last_name')))
                        <p>
                            <strong>@lang('Name'): </strong>
                            {{ array_get($ticket, 'first_name').' '.array_get($ticket, 'last_name') }}
                        </p>
                        @endif
                        @if(!is_null($transaction))
                            <p>
                                <strong>@lang('Amount'): </strong>
                                ${{ number_format(array_get($ticket, 'price'), 2) }}
                            </p>
                        @endif
                    </td>
                    <td style="border-top: 1px #000 dashed; padding: 20px;">
                        <p><img src="{{ sprintf(env('QRCODE'), sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain'))) }}qr/event-checkin?t={{ array_get($ticket, 'id') }}" alt="Qr Code" /></p>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td style="border-top: 1px #000 dashed; padding: 20px;">
                        <p>&nbsp;</p>
                        <p>Sincerely<br>{{ array_get($tenant, 'organization') }}</p>
                    </td>
                    <td style="border-top: 1px #000 dashed; padding: 20px;">&nbsp;</td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>