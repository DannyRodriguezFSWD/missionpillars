<div class="table-responsive">
    <table class="table table-striped print">
        <thead>
        <tr>
            <th>@lang('Name')</th>
            @foreach($repetitions as $repeat)
                <th class="text-center">
                    <small>{{ humanReadableDate(array_get($repeat, 'start_date')) }}</small>
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($contacts as $contact)
            <tr>
                <td>
                    <small>{{ $contact->first_name }} {{ $contact->last_name }}</small>
                </td>
                @foreach($repetitions as $repeat)
                    @if( !is_null($contact->checkedIn()->where('calendar_event_template_split_id', array_get($split, 'id'))->first()) )
                        <td class="text-center text-success">
                            <span class="icon icon-check"></span>
                        </td>
                    @else
                        <td class="text-center text-danger">
                            <span class="icon icon-close"></span>
                        </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
