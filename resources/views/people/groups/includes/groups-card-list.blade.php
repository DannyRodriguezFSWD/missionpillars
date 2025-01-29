@foreach($groups as $group)
<div class="card shadow-lg" data-groupCard="true" data-groupName="{{ $group->name }}">
    <div class="card-body clearfix">
        @if($group->cover_image)
        <img src="{{ asset('storage/groups/'.array_get($group, 'cover_image')) }}" alt="{{ array_get($group, 'name') }}" class="img-fluid mr-3 float-left" style="width: 72px; height: 72px; object-fit: cover;" />
        @else
        <i class="icon icon-people bg-primary p-4 font-2xl mr-3 float-left"></i>
        @endif
        <div class="h5 text-primary mb-0 pt-3 text-left">
            <a href="{{ route('groups.show', ['id' => $group->id]) }}">
                {{ $group->name }}
            </a>
        </div>
        <div class="text-muted font-weight-bold font-xs text-left overflow-hidden" style="max-height: 35px;">{!! $group->description !!}</div>
        <div class="pull-right position-absolute d-flex" style="top: 0;right: 0;">
            <div class="btn-group" role="group" aria-label="Basic example">
                @if (auth()->user()->can('group-signup') && !$myGroups->contains($group->id))
                {{ Form::open(['route' => ['groups.join-self', $group->uuid]]) }}
                {{ Form::hidden('start_url', request()->fullUrl()) }}
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-user-plus" data-toggle="tooltip" title="Sign Up"></i> Sign Up
                </button>
                {{ Form::close() }}
                @endif
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#share-group-modal-{{ $group->id }}">
                    <i class="fa fa-share-alt-square" data-toggle="tooltip" title="Share Link"></i> Share This Group
                </button>
            </div>
        </div>
    </div>
</div>
@include('people.groups.includes.share-group-modal')
@endforeach
