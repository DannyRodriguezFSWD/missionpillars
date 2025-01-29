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
                    <td>CRM ID</td>
                    <td>{{ $title }}</td>
                    @foreach($repetitions as $repeat)
                    <td>
                        <small>{{ humanReadableDate(array_get($repeat, 'start_date')) }}</small>
                    </td>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>{{ $contact->id }}</td>
                    <td>
                        <small>{{ $contact->first_name }} {{ $contact->last_name }}</small>
                    </td>
                    @foreach($repetitions as $repeat)
                    @if( !is_null($contact->checkedIn()->where('calendar_event_template_split_id', array_get($split, 'id'))->first()) )
                    <td>YES</td>
                    @else
                    <td>NO</td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
