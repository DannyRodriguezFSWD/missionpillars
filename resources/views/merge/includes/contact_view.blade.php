<h4>
    {{$contact->first_name . ' ' .$contact->last_name}}

</h4>
<div class="col-12">
    <div class="card bg-success text-light text-center border-light">
        <div class="card-body pt-1 pb-1">
            <div class="small text-uppercase font-weight-bold">
                Total Transactions amount
            </div>
            <div class="text-value-lg">
                ${{ number_format($completedtransactions->sum('amount')) }}
            </div>
            <div> &nbsp; </div>
        </div>
    </div>
</div>
@if(count($timelines))
    <div id="timeline_container" class="text-left ps--scrolling-x ps--scrolling-y" style="max-height: 50vh; overflow-y: auto;">
        <ul class="timeline">
            @foreach($timelines as $post)
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
                    <?php array_set($post, 'timeline_subject', substr(array_get($post, 'timeline_subject'), 0, 100))?>
                    @include('people.contacts.includes.timeline.note')
                @elseif(array_get($post, 'timeline_category') == 'form')
                    @include('people.contacts.includes.timeline.form')
                @elseif(array_get($post, 'timeline_category') == 'ticket')
                    @include('people.contacts.includes.timeline.ticket')
                @endif
            @endforeach
        </ul>
    </div>
@else
    Nothing to show
@endif