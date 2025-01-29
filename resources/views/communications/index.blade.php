@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.index') !!}
@endsection
@section('title')
    @lang('Mass Email/Print')
@endsection

@section('content')
<a href="{{ route('communications.create') }}" class="btn btn-primary mb-3">
    <i class="fa fa-commenting-o"></i> @lang('Create New Communication')
</a>

<div class="card">
    @if (request("statements"))
    <div>
        <em>Displaying only communications that include transactions. <a href="{{route('communications.index')}}">Display All</a></em>
    </div>
    @endif

    <div class="table-responsive mb-2">
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th></th>
                <th>@lang('Subject')</th>
                <th>@lang('Sent to list')</th>
                <th>@lang('Created at')</th>
                <th>@lang('# Contacts')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($messages as $message)
                <tr onclick="getCommunicationDetails('{{ route('communications.show', $message->id) }}')" class="cursor-pointer" data-href="{{ route('communications.show', $message->id) }}">
                    <td>
                        @if (array_get($message, 'last_action') === 'print')
                        <i class="fa fa-print text-primary fa-lg" data-toggle="tooltip" title="Last printed on {{ displayLocalDateTime(array_get($message, 'updated_at'))->format('D, M j Y g:i A') }}"></i>
                        @elseif (array_get($message, 'total_scheduled_emails') > 0)
                        <i class="fa fa-clock-o text-warning fa-lg" data-toggle="tooltip" title="Emails scheduled for {{ displayLocalDateTime(array_get($message, 'time_scheduled'))->format('D, M j g:i A') }}"></i>
                        @elseif (array_get($message, 'has_not_been_sent'))
                        <i class="fa fa-envelope text-success fa-lg" data-toggle="tooltip" title="Not sent yet"></i>
                        @else
                        <i class="fa fa-envelope text-success fa-lg" data-toggle="tooltip" title="Last email sent on {{ displayLocalDateTime(array_get($message, 'updated_at'))->format('D, M j Y g:i A') }}"></i>
                        @endif
                    </td>
                    <td>
                        {{ array_get($message, 'content') ? array_get($message, 'content') : array_get($message,'label') }}
                    </td>
                        <td>
                            @if ($message->print_for == 'contact')
                                @lang('Single Contact')
                            @elseif (empty(array_get($message, 'list_name')))
                                @lang('Everyone')
                            @elseif ($message->datatable_state_id)
                                <em><a class="saved-search-link" href="#" data-href="{{  route('search.contacts.state.show', $message->datatable_state_id) }}">From Saved Search</a></em>
                            @else
                                {{ array_get($message, 'list_name') }}
                            @endif
                        </td>
                        <td>
                            {{ displayLocalDateTime(array_get($message, 'created_at'))->format("n/d/Y g:i a") }}
                        </td>
                        <td>
                            {{ $message->sent_count }}
                        </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $messages->links() }}</div>
</div>

<div class="modal fade" id="communicationDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Communication Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('.saved-search-link').click(function (e) {
        e.stopPropagation();
        window.location = $(this).data('href');
    });
    
    function getCommunicationDetails(url) {
        customAjax({
            url: url,
            type: 'get',
            success: function (response) {
                $('#communicationDetailsModal .modal-body').html(response.html);
                $('#communicationDetailsModal').modal('show');
            }
        });
    }
    
    function cancelSend(url) {
        customAjax({
            url: url,
            success: function (response) {
                if (response.success) {
                    $('#communicationDetailsModal .modal-body').html(response.html);
                    Swal.fire('Scheduled emails have been canceled', '', 'success');
                }
            }
        });
    }
</script>
@endpush

@endsection
