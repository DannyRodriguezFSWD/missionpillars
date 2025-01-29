@extends('layouts.app')

@section('content')



<div class="card">
    <div class="card-header">
        @lang('Mailchimp Lists')
    </div>
    <div class="card-body">
        <div class="btn-group btn-group" role="group" aria-label="...">
            <a href="{{ route('mailchimp.addlist', ['id' => $id]) }}" class="btn btn-primary">
                <i class="icon icon-list"></i>
                @lang('Create New List')
            </a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>List Title</th>
                    <th>Company</th>
                    <th>From Name</th>
                    <th>From Email</th>
                    <th>Subject</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lists as $list)
                <tr>
                    <td>{{ $list->name }}</td>
                    <td>{{ $list->contact->company }}</td>
                    <td>{{ $list->campaign_defaults->from_name }}</td>
                    <td>{{ $list->campaign_defaults->from_email }}</td>
                    <td>{{ $list->campaign_defaults->subject }}</td>
                    <td>
                        <a class="btn btn-link" href="{{ route('mailchimp.members', ['id' => $id, 'list' => $list->id, 'listname' => $list->name]) }}">
                            <i class="icon icon-info"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="mailchimp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-info modal-lg" role="document">
        {{ Form::open(['route' => 'integrations.store']) }}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Mailchimp')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('API_KEY', 'API Key') }}
                    {{ Form::text('API_KEY', null, ['class' => 'form-control']) }}
                    {{ Form::hidden('service', 'Mailchimp') }}
                    {{ Form::hidden('description', 'Email plattform integration') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
@endsection
