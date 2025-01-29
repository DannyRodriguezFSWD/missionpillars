@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <h4 class="mb-0">{{ $total }}</h4>
        <p>@lang('API Keys')</p>
        <div class="btn-group btn-group" role="group" aria-label="...">
            <div class="input-group-btn">
                @can('create',\App\Models\OauthAccessToken::class)
                    <a href="{{ route('api.create') }}" class="btn btn-primary">
                        <i class="fa fa-map-signs"></i>
                        @lang('Add New API Key')
                        <span class="caret"></span>
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>@lang('API Key')</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($keys as $key)
                <tr>
                    <td>{{ $key->name }}</td>
                    <td>
                        @can('show',\App\Models\OauthAccessToken::class)
                            <a class="btn btn-link" href="{{ route('api.show', ['id' => $key->id]) }}" title="@lang('Share API Key')">
                                <span class="icon icon-share"></span>
                            </a>
                        @endcan
                    </td>
                    <td>
                        @can('delete',\App\Models\OauthAccessToken::class)
                            {{ Form::model($key, ['route' => ['api.destroy', $key->id], 'method' => 'delete', 'id'=>'delete-form-'.$key->id]) }}
                            {{ Form::hidden('uid',  Crypt::encrypt($key->id)) }}
                            <button type="button" class="btn btn-link text-danger delete" data-name="{{$key->name}}" data-form="#delete-form-{{$key->id}}" data-toggle="modal" data-target="#delete-modal">
                                <span class="fa fa-trash"></span>
                            </button>
                            {{ Form::close() }}
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>
@include('integration.api.includes.delete-modal')
@endsection