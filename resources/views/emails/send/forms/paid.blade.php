@if (!is_null($manager))
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #74787e;">
    <tr>
        <td>
            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                @if($form->custom_header)
                    <tr>
                        <td>
                            {!! $form->custom_header !!}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <h3>
                            {{ array_get($tenant, 'organization') }} - @lang('Payment Receipt')
                        </h3>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>{{ displayLocalDateTime(array_get($entry, 'created_at'))->toDayDateTimeString() }}</h5>
                    </td>
                </tr>
                @if(!is_null(array_get($entry, 'transaction')))
                <tr>
                    <td>
                        {{ array_get($entry, 'transaction.template.alt_id') }}
                        <strong>@lang('Transaction'): </strong>
                        {{ array_get($entry, 'transaction.getAltIds.0.alt_id') }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>
                            Dear {{ array_get($manager, 'first_name') }} {{ array_get($manager, 'last_name') }}<br>
                            You have received a payment of ${{ number_format(array_get($entry, 'transaction.splits.0.amount', 0), 2) }} from {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}.
                        </p>
                        <p>
                            You can view the form here:
                            <a href="{{ $entry_url }}">{{ $entry_url }}</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>Sincerely<br>{{ array_get($tenant, 'organization') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@else
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="color: #74787e;">
    <tr>
        <td>
            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0">
                @if($form->custom_header)
                    <tr>
                        <td>
                            {!! $form->custom_header !!}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <h3>
                            {{ array_get($tenant, 'organization') }} - @lang('Payment Receipt')
                        </h3>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>{{ displayLocalDateTime(array_get($entry, 'created_at'))->toDayDateTimeString() }}</h5>
                    </td>
                </tr>
                @if(!is_null(array_get($entry, 'transaction')))
                <tr>
                    <td>
                        {{ $entry->transaction->template->alt_id }}
                        <strong>@lang('Transaction'): </strong>
                        {{ array_get($entry, 'transaction.getAltIds.0.alt_id') }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>
                            Dear {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}<br>
                            Thank you very much for your payment of ${{ number_format(array_get($entry, 'transaction.splits.0.amount', 0), 2) }} to {{ array_get($tenant, 'organization') }}.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>Sincerely<br>{{ array_get($tenant, 'organization') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif