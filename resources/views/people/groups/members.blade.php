<div class="row">
    <div class="col-12">
        <h4 class="mb-0">
            <i class="fa fa-users"></i> <span data-groupMembersCount="true">{{ $total }}</span>
            @if (auth()->user()->can('group-update'))
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#members-edit-modal" onclick="manageMembers();">
                <i class="fa fa-users"></i> @lang('Manage Members')
            </button>
            @endif
        </h4>
        <p>@lang('Members')</p>
    </div>
</div>

@include('people.contacts.includes.contacts-directory')
