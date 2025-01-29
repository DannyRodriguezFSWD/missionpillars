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
                    <th>@lang('Contact')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Event')</th>
                    <th>@lang('Event Start')</th>
                    <th>@lang('Event End')</th>
                    <th>@lang('Ticket Nr')</th>
                    <th>@lang('Ticket')</th>
                    <th>@lang('Ticket Price')</th>
                    <th>@lang('Paid')</th>
                    <th>@lang('Ticket Purchase Timestamp')</th>
                    @if($whose_ticket)
                        <th>@lang('Ticket For')</th>
                        <th>@lang('Ticket For Email')</th>
                    @endif
                    <th>@lang('Form Filled')</th>
                    <th>Check In</th>
                    @foreach($allFormsNamesAndLabels as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                    <th>Extras</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registries as $registry)
                    @if( count(array_get($registry, 'tickets')) > 0 )
                        @include('events.includes.export-with-tickets')
                    @else
                        @include('events.includes.export-without-tickets')
                    @endif
                @endforeach
            </tbody>
        </table>
    </body>
</html>
