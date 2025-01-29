@extends('layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        @lang('Export mambers to Mailchimp List: ')
        {{ $listName }}
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('Last Name')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Add to Mailchimp')</th>
                    <th>@lang('Tags')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>{{ $contact->first_name }}</td>
                    <td>{{ $contact->last_name }}</td>
                    <td>{{ $contact->email_1 }}</td>
                    <td>
                        {{ Form::open(['route' => ['mailchimp.store', $id, $listId, 'listname='.$listName]]) }}
                        {{ Form::hidden('cid', Crypt::encrypt($contact->id)) }}
                        <button type="submit" class="btn btn-link">
                            <i class="icon icon-paper-plane"></i>
                        </button>
                        {{ Form::close() }}
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><small>{{ $contact->first_name }} is tagged in</small></td>
                    <td colspan="2">
                        @foreach($contact->tags as $tag)
                        <span class="badge badge-pill badge-success p-1">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $contacts->appends(['listname' => $listName])->links() }}
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
