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
                    <th>@lang('Chart of account')</th>
                    <th>@lang('Group Name')</th>
                    <th>@lang('Fund Name')</th>
                    <th>@lang('Account Number')</th>
                    <th>@lang('Account Name')</th>
                    <th>@lang('Amount $')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement as $account)
                <tr>
                    <td>{{ array_get($account, 'chart_of_account') }}</td>
                    <td>{{ array_get($account, 'group_name') }}</td>
                    <td>{{ array_get($account, 'fund_name') }}</td>
                    <td>{{ array_get($account, 'account_number') }}</td>
                    <td>{{ array_get($account, 'account_name') }}</td>
                    <td>{{ floatval(array_get($account, 'amount')) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
