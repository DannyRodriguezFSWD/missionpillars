@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('groups.index') !!}
@endsection
@section('content')

@include('people.groups.includes.functions')

@push('scripts')
<script src="{{ asset('js/tags/events.js')}}"></script>
@endpush
@push('styles')
<link href="{{ asset('css/tree.css')}}" rel="stylesheet">
@endpush


@if ($errors->has('group'))
<div class="alert alert-danger">
    {{ $errors->first('group') }}
</div>
@endif
@if ($errors->has('folder'))
<div class="alert alert-danger">
    {{ $errors->first('folder') }}
</div>
@endif

<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Search Small Groups" id="searchGroups">
        </div>
    </div>
    
    <div class="col-md-6 text-right">
        @if (auth()->user()->can('events-view') && auth()->user()->can('group-view'))
        <a href="{{ route('checkin.index') }}" target="_blank" class="btn btn-primary">
            <i class="fa fa-check-circle-o"></i>
            @lang('People Checkin')
        </a>
        @endif
        <a href="#" data-toggle="modal" class="btn btn-primary" data-target="#share-groups-modal">
            <i class="fa fa-share-alt-square"></i>
            @lang('Share Signup Link')
        </a>
        @if (auth()->user()->can('group-create'))
        <a href="{{ route('groups.create', ['id' => $root->id]) }}" class="btn btn-success">
            <i class="fa fa-plus"></i>
            @lang('New Group')
        </a>
        @endif
    </div>
</div>

<div id="groupContainer">
    @include('people.groups.includes.groups-card-list')
</div>

@include('people.groups.includes.share-groups-modal')

@push('scripts')
<script>
    $('#searchGroups').keyup(customDelay(function () {
        let search = $(this).val();

        customAjax({
            url: "{{ route('groups.search') }}",
            data: {
                search: search
            },
            success: function (data) {
                $('#groupContainer').html('');

                if (data.count > 0) {
                    $('#groupContainer').html(data.html);
                } else {
                    $('#groupContainer').html('<div class="alert alert-info">There are no groups that matched your search.</div>');
                }

                $('[name="last_page"]').val(data.lastPage);
                $('[name="last_search"]').val(new Date().getTime());
            }
        });
    }, 500));
    
    $('#groupContainer').scrollPaginate({
        url: '{{ route('groups.search') }}',
        lastPage: {{ $groups->lastPage() }},
        search: $('#searchGroups')
    });
</script>
@endpush

@endsection
