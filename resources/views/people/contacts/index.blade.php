@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts') !!}
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4 class="mb-0">{{ $total }}</h4>
                <p>@lang('Contacts')</p>
                <div class="btn-group btn-group" role="group" aria-label="...">
                    @can('create',\App\Models\Contact::class)
                    <a href="{{ route('contacts.create') }}" class="btn btn-primary">
                        <i class="icon icon-user-following"></i>
                        @lang('Add New Contact')
                    </a>
                    @endcan
                    <button class="btn btn-primary" data-toggle="modal" data-target="#select-contact-modal">
                        <i class="icon icon-magnifier"></i>
                        @lang('Search Contact')
                    </button>
                    <a href="{{ route('search.contacts') }}" class="btn btn-primary">
                        {{-- <i class="icon "></i> --}}
                        @lang('Advanced Search')
                    </a>
                </div>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('contacts.index', ['sort' => 'firstname', 'order' => $nextOrder]) }}">
                                @lang('Firstname')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'firstname' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'firstname' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('contacts.index', ['sort' => 'lastname', 'order' => $nextOrder]) }}">
                                @lang('Lastname')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'lastname' )
                                <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'lastname' )
                                <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('contacts.index', ['sort' => 'email', 'order' => $nextOrder]) }}">
                                @lang('Email')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'email' )
                                <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'email' )
                                <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>Cellphone</th>
                        <!--<th>&nbsp;</th>-->
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contacts as $contact)
                    <tr 
                        @can('show',$contact)
                            class="clickable-row" data-href="{{ route('contacts.show', ['id'=>$contact->id]) }}"
                        @endcan
                    >
                        <td>
                            {{ $contact->first_name }}
                        </td>
                        <td>
                            {{ $contact->last_name }}
                        </td>
                        <td>
                            {{ $contact->email_1 }}
                        </td>
                        <td>
                            {{ $contact->cell_phone }}
                        </td>
                        <!--
                        <td>
                            <a href="{{ route('contacts.edit', ['id'=>$contact->id]) }}" class="btn btn-link">
                                <span class="fa fa-edit"></span>
                            </a>
                        </td>
                        <td>
                            {{ Form::model($contact, ['route' => ['contacts.destroy', $contact->id], 'method' => 'delete', 'id'=>'delete-form-'.$contact->id]) }}
                            {{ Form::hidden('uid',  Crypt::encrypt($contact->id)) }}
                            <button type="button" class="btn btn-link text-danger delete" data-name="{{$contact->first_name}}" data-form="#delete-form-{{$contact->id}}" data-toggle="modal" data-target="#delete-modal">
                                <span class="fa fa-trash"></span>
                            </button>
                            {{ Form::close() }}
                        </td>
                        -->
                        <td>
                            @can('show',$contact)
                            <span class="icon icon-arrow-right"></span>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="card-body">
                @if( isset($nextOrder) && isset($sort) )
                {{ $contacts->appends(['sort' => $sort, 'order' => $order])->links() }}
                @else
                {{ $contacts->links() }}
                @endif
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>
<!--/.row-->

@include('people.contacts.includes.delete-modal')
@include('people.contacts.includes.select-contact-modal')
@endsection
