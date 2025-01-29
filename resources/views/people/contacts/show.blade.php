@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.show',$contact) !!}
@endsection
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @push('styles')
        <link href="{{ asset('css/timeline.custom.css') }}?t={{ time() }}" rel="stylesheet">
    @endpush()
    
    <div class="card">
        <div class="card-header">
            @include('people.contacts.includes.card-header')
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h2>Profile</h2>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    {{-- Transactions --}}
                    @include('people.contacts.includes.transactions.pane')
                    
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    @include('people.contacts.includes.contact-information')
                </div>
            </div>
            
            @if (array_get($contact, 'type') === 'organization')
            <div class="row">
                <div class="col-6">
                    @include('people.contacts.includes.contact-relatives')
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                @lang('Tags')
                            </h5>
                            <p>
                                @foreach($contact->tags as $tag)
                                    <a href="{{ route('tags.contacts', ['id' => $tag->id]) }}" class="p-1"> <span class="badge badge-pill badge-primary p-2 mb-2">
                                        <i class="icon icon-tag"></i> {{ $tag->name }}
                                    </span></a>
                                @endforeach
                            </p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('contacts.tags', $contact)}}" class="btn btn-primary"><i class="fa fa-edit"></i> @lang('Edit Tags')</a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            @else
            <div class="row">
                <div class="col-12">
                    <div class="card-deck">
                        @include('people.contacts.includes.contact-family')
                        @include('people.contacts.includes.contact-relatives')
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <div class="card-deck">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    @lang('Tags')
                                </h5>
                                <p>
                                    @foreach($contact->tags as $tag)
                                        <a href="{{ route('tags.contacts', ['id' => $tag->id]) }}" class="p-1"> <span class="badge badge-pill badge-primary p-2 mb-2">
                                            <i class="icon icon-tag"></i> {{ $tag->name }}
                                        </span></a>
                                    @endforeach
                                </p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="{{ route('contacts.tags', $contact)}}" class="btn btn-primary"><i class="fa fa-edit"></i> @lang('Edit Tags')</a>
                            </div>
                        </div>
                        @include('people.contacts.includes.contact-child-checkin-note')
                    </div>
                </div>
            </div>
            @endif
            
            @if(count($customFields) > 0)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                @lang('Custom Fields')
                            </h5>
                            <div class="row">
                                @foreach($customFields as $customField)
                                <div class="col-lg-4 col-md-6">
                                    <div class="list-group-item">
                                        <i>{{ array_get($customField, 'customField.name') }}</i>: {{ array_get($customField, 'value') }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="row mb-2">
                @if(auth()->user()->can('contact-timeline'))
                <div class="col-sm-8 col-lg-9 pull-sm-4 pull-lg-3">
                    {{-- Timeline --}}
                    <h3>Timeline</h3>
                    <div class="row mb-2">
                        <ul class="timeline"></ul>
                    </div>
                    <div class="row mb-2">
                        <button name="more" type="button" class="btn btn-info col">
                            @lang('Load More') ...
                        </button>
                    </div>
                </div>
                @endif
                {{-- Tasks --}}
                <div class="col-sm-4 col-lg-3 push-sm-8 push-lg-9 mb-4" style="border-left: 1px solid rgba(0, 0, 0, 0.1)">
                    <h3>@lang('Tasks')</h3>
                    @can('update',$contact)
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#task-modal">
                            <span class="fa fa-check-square"></span>
                            @lang('Add Task')
                        </button>
                    @endcan
                    <p class="pb-3">&nbsp;</p>
                    @include('tasks.includes.list')
                </div>
            </div>
        </div>
    </div>
    
    
    @include('tasks.includes.create')
    @include('people.contacts.includes.view-email-timeline')
    @include('people.contacts..includes.resubscribe-modal')
    @include('people.contacts..includes.resubscribe-phone-modal')
    
    @push('scripts')
        <script type="text/javascript">
        (function () {
            var data = {page: 1};
            
            $('button[name="more"]').on('click', function (e) {
                data.page += 1;
                loadMore(data);
            });
            
            function loadMore(data) {
                $.get("{{ route('contacts.timeline', ['id' => array_get($contact, 'id')]) }}", data, function (result) {
                    if (result == '' && data.page > 1) {
                        Swal.fire('No more actions','','info');
                    } else {
                        $('ul.timeline').append(result);
                    }
                }).fail(function (result) {
                    console.log(result.responseText);
                    Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
                });
            }
            
            loadMore(data);
        })();
        </script>
    @endpush

@endsection
