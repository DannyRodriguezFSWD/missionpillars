<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="{{ public_path('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .title {
            text-align: center;
        }
        .borderless .table-title {
            border-bottom: #afafaf solid 1px;
            border-top: #afafaf solid 1px;
        }
        .blank_row {
            height: 46px;
        }
        .bold-totals {
            font-weight: bold;
        }
        table th {
            text-align: center;
        }
        .borderless td, .borderless th {
            border: none;
        }
    </style>
</head>
<body>
    <div class="title">
        <h3>Income Statement</h3>
    </div>
    <div class="table-responsive mt-3">
        <table class="table borderless">
            <thead class="thead-inverse">
                <tr>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-title">
                   <td colspan="3"><h4>Income</h4></td>
                </tr>
                @foreach($report['income'] as $account)
                    @if ($account['balance'] !== '$0.00' || ($account['balance'] == '$0.00' && $show_zeros == 'true'))
                    <tr>
                        <td>{{ $account['number'] }}</td>
                        <td>{{ $account['name'] }}</td>
                        <td class="text-right">{{ $account['balance'] }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="2" class="bold-totals">Total income</td>
                    <td class="bold-totals text-right totals-number">{{ $totals['income'] }}</td>
                </tr>
                <tr class="table-title">
                    <td colspan="3"><h4>Expense</h4></td>
                 </tr>
                 @foreach($report['expense'] as $account)
                     @if ($account['balance'] !== '$0.00' || ($account['balance'] == '$0.00' && $show_zeros == 'true'))
                     <tr>
                         <td>{{ $account['number'] }}</td>
                         <td>{{ $account['name'] }}</td>
                         <td class="text-right">{{ $account['balance'] }}</td>
                     </tr>
                     @endif
                 @endforeach
                 <tr>
                     <td colspan="2" class="bold-totals">Total expense</td>
                     <td class="bold-totals text-right totals-number">{{ $totals['expense'] }}</td>
                 </tr>
                 <tr>
                     <td colspan="2" class="bold-totals">Net Income(Loss)</td>
                     <td class="bold-totals text-right totals-number">{{ $totals['netIncome'] }}</td>
                 </tr>
            </tbody>
        </table>
    </div>
</body>
</html>