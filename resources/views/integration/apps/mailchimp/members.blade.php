@extends('layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        @lang('Mailchimp List: ')
        {{ $listName }}
    </div>
    <div class="card-body">
        <div class="btn-group btn-group" role="group" aria-label="...">
            <a href="{{ route('mailchimp.addtags', ['id' => $id, 'list' => $list->list_id, 'listname' => $listName]) }}" class="btn btn-primary">
                <i class="icon icon-tag"></i>
                Add members by tags
            </a>
            <a href="{{ route('mailchimp.addmembers', ['id' => $id, 'list' => $list->list_id, 'listname' => $listName]) }}" class="btn btn-primary">
                <i class="icon icon-user-follow"></i>
                Add members manually
            </a>
            <a href="{{ route('mailchimp.deletetags', ['id' => $id, 'list' => $list->list_id, 'listname' => $listName]) }}" class="btn btn-danger">
                <i class="fa fa-trash"></i>
                Delete members by tags
            </a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Open rate</th>
                    <th>Click rate</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list->members as $member)
                <tr>
                    <td>{{ $member->merge_fields->FNAME }}</td>
                    <td>{{ $member->merge_fields->LNAME }}</td>
                    <td>{{ $member->email_address }}</td>
                    <td>
                        <span class="badge badge-pill badge-info p-2">
                            {{ $member->status }}
                        </span>
                    </td>
                    <td class="text-center">{{ $member->stats->avg_open_rate }}%</td>
                    <td class="text-center">{{ $member->stats->avg_click_rate }}%</td>
                    <th>
                        <button type="button" class="btn btn-link text-danger" data-name="{{$member->merge_fields->FNAME}}" 
                                data-href="{{ route('mailchimp.unsubscribecontact', ['id' => $id, 'list' => $list->list_id, 'member' => md5(strtolower($member->email_address)), 'listname' => $listName]) }}"
                                data-toggle="modal" data-target="#delete-modal">
                            <span class="fa fa-trash"></span>
                        </button>
                    </th>
                </tr>
                @endforeach
            </tbody>
        </table>

        <ul class="pagination">
            @foreach($pagination as $page)
            <li>
                <a href="{{ route('mailchimp.members', ['id' => $id, 'list' => $list->list_id, 'listname' => $listName, 'offset'=> $page->offset]) }}">
                    {{ $page->number }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Mailchimp')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <p>@lang('Are you sure you want to unsubscribe selected member from mailchimp list?')</p>
                    <small>@lang("This action can't be undone")</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                <button type="submit" class="btn btn-warning" id="button-delete">@lang('Unsubscribe')</button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script type="text/javascript">
    var url;

    $(function () {
        $('#delete-modal').on('shown.coreui.modal', function (e) {
            var button = $(e.relatedTarget);
            url = button.data('href');
        });

        $('#button-delete').on('click', function (e) {
            window.location.href = url;
        });
    });
</script>
@endpush

@endsection
