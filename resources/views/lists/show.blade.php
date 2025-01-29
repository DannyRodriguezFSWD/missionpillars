@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body text-right pb-2">
                <div class="" id="floating-buttons">
                    @can('update',$list)
                        <a href="{{ route('lists.edit', ['id' => array_get($list, 'id')]) }}" class="btn btn-primary">
                            <i class="fa fa-edit"></i> @lang('Edit')
                        </a>
                    @endcan
                    @can('delete',$list)
                        {{ Form::model($list, ['route' => ['lists.destroy', $list->id], 'method' => 'delete', 'id'=>'delete-form-'.$list->id]) }}
                        {{ Form::hidden('uid',  Crypt::encrypt($list->id)) }}
                        <button type="button" class="btn btn-danger delete" data-name="{{$list->name}}"
                                data-form="#delete-form-{{$list->id}}" data-toggle="modal" data-target="#delete-modal">
                            <span class="fa fa-trash"></span> @lang('Delete')
                        </button>
                        {{ Form::close() }}
                    @endcan
                </div>
            </div>
<!--
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('lists.index') }}">
                        @lang('Lists')
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ array_get($list, 'name') }}</li>
            </ol>
-->
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ $total }} <small>@lang('Contacts') in {{ array_get($list, 'name') }}</small></h3>
                    </div>
                </div>
                <p>&nbsp;</p>
                <div class="row">
                    <div class="col-sm-12">
                        {{ Form::open(['route' => ['lists.search', $list->id], 'method' => 'GET']) }}
                        <div class="input-group">
                            <input type="hidden" name="action" value="search">
                            <input type="text" name="keyword" class="form-control" autocomplete="off" required="">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('lists.show', ['id' => $list->id, 'sort' => 'firstname', 'order' => $nextOrder]) }}">
                                @lang('Firstname')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'firstname' )
                                <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'firstname' )
                                <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('lists.show', ['id' => $list->id, 'sort' => 'lastname', 'order' => $nextOrder]) }}">
                                @lang('Lastname')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'lastname' )
                                <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'lastname' )
                                <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('lists.show', ['id' => $list->id, 'sort' => 'email', 'order' => $nextOrder]) }}">
                                @lang('Email')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'email' )
                                <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'email' )
                                <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>Cell Phone</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contacts as $contact)
                    <tr class="clickable-row" data-href="{{ route('contacts.show', ['id' => array_get($contact, 'id')]) }}" data-target="blank">
                        <td>{{ $contact->first_name }}</td>
                        <td>{{ $contact->last_name }}</td>
                        <td>{{ $contact->email_1 }}</td>
                        <td>{{ $contact->cell_phone }}</td>
                        <td class="text-right">
                            <span class="icon icon-arrow-right"></span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $contacts->links() }}
            <div class="card-footer">
                &nbsp;
            </div>
        </div>
    </div>
    <!--/.col-->
</div>
<!--/.row-->
@include('lists.includes.delete-modal')
@endsection
