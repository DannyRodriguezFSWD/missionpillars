<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{{ $filename }}</title>
        @if ($format == 'pdf')
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        @endif
    </head>
    <body>
        @if (in_array(array_get($report, 'id'), [0, 2]))
            @include('reports.crm.includes.tables.report0')
        @elseif (array_get($report, 'id') == 1)
            @include('reports.crm.includes.tables.report1_1')
            @include('reports.crm.includes.tables.report1_2')
        @elseif (array_get($report, 'id') == 3)
            @include('reports.crm.includes.tables.report3')
        @elseif (array_get($report, 'id') == 4)
            @include('reports.crm.includes.tables.report4')
        @endif
    </body>
</html>
