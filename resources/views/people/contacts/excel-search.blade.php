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
                    @foreach($columns as $column)
                        <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($i=0; $i<count($columns); $i++)
                        @if (in_array($columns[$i], $numberColumns))
                            <th data-format="$ #,##0_-">{{ '=sum('.$alphabet[$i].'3:'.$alphabet[$i].(count($contacts)+2).')' }}</th>
                        @else
                            <th></th>
                        @endif
                    @endfor
                    <th>Totals</th>
                </tr>
                @foreach($contacts as $contact)
                <tr>
                    @foreach($columns as $column)
                        @if (in_array($column, $numberColumns))
                            <td data-format="$ #,##0_-">{{ toCurrencyReverse($contact->$column) }}</td>
                        @elseif ((in_array($column, $dateColumns)))
                            <td data-format="mm/dd/yyyy">{{ $contact->$column }}</td>
                        @else
                            <td>{{ $contact->$column }}</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
