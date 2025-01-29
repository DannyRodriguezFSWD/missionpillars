<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{{ $filename }}</title>
    </head>
    <body>
        <table>
            <thead>
                <tr></tr>
                <tr>
                    <th colspan="2">@lang('Attendance Report for the') {{ array_get($group, 'name') }} @lang('Group')</th>
                </tr>
                <tr>
                    <td colspan="2">
                        {{ $fromDate->format('M j, Y') }} - {{ $toDate->format('M j, Y') }}
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <th>@lang('First Name')</th>
                    <th>@lang('Last Name')</th>
                    <th>@lang('Last Attended')</th>
                    @foreach ($reportDates as $date)
                    <th>
                        {{ $date['start']->format('n/j/Y') }} - {{ $date['end']->format('n/j/Y') }}
                    </th>
                    @endforeach
                    <th>@lang('Weeks Attended')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportContacts as $contact)
                <tr>
                    <td>{{ array_get($contact, 'first_name') }}</td>
                    <td>{{ array_get($contact, 'last_name') }}</td>
                    <td>
                        @if (array_get($contact, 'last_attendance'))
                            {{ array_get($contact, 'last_attendance')->format('n/j/Y') }}
                        @endif
                    </td>
                    @foreach ($reportDates as $date)
                        @isset($contact[$date['start']->format('Y-m-d')])
                            <td>{{ $contact[$date['start']->format('Y-m-d')] }}</td>
                        @else
                            <td>0</td>
                        @endisset
                    @endforeach
                    <td>{{ array_get($contact, 'weeks_attended') }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="2">@lang('Total Attendance')</th>
                    <td></td>
                    @foreach ($reportDates as $date)
                        @isset($totalAttendance[$date['start']->format('Y-m-d')])
                            <td>{{ $totalAttendance[$date['start']->format('Y-m-d')] }}</td>
                        @else
                            <td>0</td>
                        @endisset
                    @endforeach
                    <td></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
