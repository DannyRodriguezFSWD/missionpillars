<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('Invoice') #{{ array_get($invoice, 'reference') }}</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
    
    <style type="text/css">
        html, body{
            background: #ffffff;
            font-family: 'Quicksand', sans-serif;
        }
        .mp-invoice-header,
        .table-striped tbody tr:nth-of-type(odd){
            background: #f0f3f5;
        }
        p{ font-size: 1em; }
        table,
        table>*{ font-size: 0.8em; }
    </style>
</head>
<body>
    <table class="table">
        <tr>
            <td>
                <p style="font-weight: bold;" class="mb-0 mt-4">MISSION PILLARS</p>
                <p style="font-size: 0.6em;">Church Management System</p>
                <p class="mb-0">@lang('Invoice number') {{ array_get($invoice, 'reference') }}</p>
                <p>
                    @lang('Invoice Date:')
                    {{ displaylocalDateTime( array_get($invoice, 'billing_to') )->format('F d, Y') }}
                </p>
                <p>{{ array_get($invoice, 'module_name') }}</p>
            </td>
            <td>
                <p class="text-right">{{ array_get($tenant, 'organization') }}</p>
                <div style="background: #303640; color: #ffffff;" class="p-4">
                    <p>@lang('Total'):</p>
                    <h6 style="text-align: right;">${{ array_get($invoice, 'total_amount') }}</h6>
                </div>
            </td>
        </tr>
    </table>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>@lang('Description')</th>
                <th class="text-center">@lang('Amount')</th>
            </tr>
        </thead>
        <tbody>
            @foreach (array_get($invoice, 'details', []) as $detail)
            <tr>
                <td>{{ array_get($detail, 'description') }}</td>
                <td class="text-right">${{ array_get($detail, 'amount') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <table class="table">
        <tr>
            <td class="text-right">
                <h6>
                    @lang('Total'): 
                    ${{ array_get($invoice, 'total_amount') }}
                </h6>
            </td>
        </tr>
    </table>
    
</body>
</html>
