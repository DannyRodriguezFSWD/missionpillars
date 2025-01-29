<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{{ $filename }}</title>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>@lang('Transaction ID')</th>
                    <th>@lang('Amount $')</th>
                    <th>@lang('Purpose')</th>
                    <th>@lang('Fundraiser')</th>
                    <th>@lang('Contact')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Time')</th>
                    <th>@lang('Channel')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Account Number Last Four')</th>
                    <th>@lang('Transaction Type')</th>
                    <th>@lang('Comment From Donor')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ array_get($transaction, 'transaction.id') }}</td>
                    <td>{{ floatval(array_get($transaction, 'amount')) }}</td>
                    <td>{{ array_get($transaction, 'purpose.name') }}</td>
                    <td>{{ array_get($transaction, 'campaign.name') }}</td>
                    <td>
                        {{ array_get($transaction, 'transaction.contact.full_name') }}
                    </td>
                    <td>
                        {{ array_get($transaction, 'transaction.contact.email_1','') }}
                    </td>
                    <td>{{ displayLocalDateTime(array_get($transaction, 'transaction.transaction_initiated_at')) }}</td>
                    <td>{{ array_get($transaction, 'transaction.channel') }}</td>
                    <td>{{ array_get($transaction, 'transaction.status') }}</td>
                    <td>{{ array_get($transaction, 'transaction.paymentOption.category') }}</td>
                    <td>{{ array_get($transaction, 'transaction.paymentOption.last_four') }}</td>
                    <td>{{ array_get($transaction, 'transaction.transaction_type') }}</td>
                    <td>{{ array_get($transaction, 'transaction.comment') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
