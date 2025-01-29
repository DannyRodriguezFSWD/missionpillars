<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0">

                <!-- Email Body -->

                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0">

                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <h3>{{ array_get($tenant, 'organization') }} - @lang('Purchase Receipt')</h3>
                                </td>
                                <td class="content-cell text-right">
                                    <h3>{{ humanReadableDate(array_get($ticket, 'created_at')) }}</h3>
                                </td>
                            </tr>
                        </table>
                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            <tr>
                                <td>
                                    <table width="100%">
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

                                    </table>
                                </td>
                            </tr>
                        </table>



                        <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                            <tr>
                                <td >
                                    <strong>@lang('Event'): </strong>
                                    {{ array_get($split, 'template.name') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>@lang('Dates'): </strong>
                                    @if( array_get($split, 'template.is_all_day') )
                                    {{ humanReadableDate(array_get($split, 'start_date')) }}
                                    @else
                                    {{ humanReadableDate(array_get($split, 'start_date')) }},
                                    {{ date('H:i a', strtotime(array_get($split, 'start_date'))) }}
                                    -
                                    {{ humanReadableDate(array_get($split, 'end_date')) }},
                                    {{ date('H:i a', strtotime(array_get($split, 'end_date'))) }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <br>
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
                            <tr>
                                <td>
                                    <p>&nbsp;</p>
                                    <p>Sincerely<br>{{ array_get($tenant, 'organization') }}</p>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <p>&nbsp;</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>