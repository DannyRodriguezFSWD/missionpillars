@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('widgets.back')
                </div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('lists.index') }}">
                            @lang('Lists')
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('lists.show', ['id' => $list->id]) }}">
                            {{ $list->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">@lang('Search result')</li>
                </ol>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $contact)
                        <tr>
                            <td>{{ $contact->first_name }}</td>
                            <td>{{ $contact->last_name }}</td>
                            <td>{{ $contact->email_1 }}</td>
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

@endsection
