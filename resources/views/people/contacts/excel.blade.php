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
                    <th>@lang('First Name')</th>
                    <th>@lang('Last Name')</th>
                    <th>@lang('Primary Email')</th>
                    <th>@lang('Secondary Email')</th>
                    <th>@lang('Cell Phone')</th>
                    <th>@lang('City')</th>
                    <th>@lang('Region')</th>
                    <th>@lang('Postal Code')</th>
                    <th>@lang('Address')</th>
                    <th>@lang('Date Of Birth')</th>
                    <th>@lang('Gender')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>{{ array_get($contact, 'first_name') }}</td>
                    <td>{{ array_get($contact, 'last_name') }}</td>
                    <td>{{ array_get($contact, 'email_1') }}</td>
                    <td>{{ array_get($contact, 'email_2') }}</td>
                    <td>{{ array_get($contact, 'cell_phone') }}</td>
                    <td>{{ array_get($contact, 'addresses.0.city') }}</td>
                    <td>{{ array_get($contact, 'addresses.0.region') }}</td>
                    <td>{{ array_get($contact, 'addresses.0.postal_code') }}</td>
                    <td>{{ array_get($contact, 'addresses.0.mailing_address_1') }}</td>
                    <td>
                        @if (array_get($contact, 'dob'))
                        {{ date('m/d/Y', strtotime(array_get($contact, 'dob'))) }}
                        @endif
                    </td>
                    <td>{{ array_get($contact, 'gender') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
