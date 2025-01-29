@foreach($timeline as $post)
    @if(array_get($post, 'timeline_status') == 'complete')
        @php $status = 'success' @endphp
    @elseif(array_get($post, 'timeline_status') == 'pending')
        @php $status = 'warning' @endphp
    @elseif(array_get($post, 'timeline_status') == 'failed')
        @php $status = 'danger' @endphp
    @elseif(array_get($post, 'timeline_status') == 'checkin')
        @php $status = 'primary' @endphp
    @else
        @php $status = 'default' @endphp
    @endif
    
    @if(array_get($post, 'timeline_category') == 'transaction')
        @if(auth()->user()->can('transaction-view'))
            @include('people.contacts.includes.timeline.transactions')
        @endif
    @elseif(array_get($post, 'timeline_category') == 'email_sent')
        @include('people.contacts.includes.timeline.email')
    @elseif(array_get($post, 'timeline_category') == 'printout')
        @include('people.contacts.includes.timeline.printout')
    @elseif(array_get($post, 'timeline_category') == 'sms_sent')
        @include('people.contacts.includes.timeline.sms')
    @elseif(array_get($post, 'timeline_category') == 'group')
        @include('people.contacts.includes.timeline.group')
    @elseif(array_get($post, 'timeline_category') == 'event')
        @include('people.contacts.includes.timeline.event')
    @elseif(array_get($post, 'timeline_category') == 'checkin')
        @include('people.contacts.includes.timeline.checkin')
    @elseif(array_get($post, 'timeline_category') == 'task')
        @include('people.contacts.includes.timeline.task')
    @elseif(array_get($post, 'timeline_category') == 'note')
        @include('people.contacts.includes.timeline.note')
    @elseif(array_get($post, 'timeline_category') == 'form')
        @include('people.contacts.includes.timeline.form')
    @elseif(array_get($post, 'timeline_category') == 'ticket')
        @include('people.contacts.includes.timeline.ticket')
    @endif
@endforeach
