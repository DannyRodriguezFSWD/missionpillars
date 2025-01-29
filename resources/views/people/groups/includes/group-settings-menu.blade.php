<div class="col-md-2 bg-white pt-2 pb-4 mx-3 mx-md-0 vertical-menu-container">
    <div class="vertical-menu no-print">
        <h3 class="text-center mb-2">{{array_get($group, 'name')}}</h3>
        <a href="{{ route('groups.show', ['id' => $group->id]) }}" class="{{ Request::route()->getName() == 'groups.members' ? 'active' : '' }}">
            <i class="fa fa-users"></i> @lang('Members')
        </a>
        @if (auth()->user()->can('group-update'))
        <a href="{{ route('groups.edit', ['id' => array_get($group, 'id')]) }}" class="{{ Request::route()->getName() == 'groups.edit' ? 'active' : '' }}">
            <span class="icon icon-settings"></span> @lang('Settings')
        </a>
        @endif
        <a href="#" data-toggle="modal" data-target="#share-group-modal-{{ $group->id }}">
            <span class="fa fa-share-alt"></span> @lang('Share URL')
        </a>
        @if(Route::is('groups.edit'))
            <button class="btn btn-block btn-success" type="submit"><i class="fa fa-save"></i> Save Group</button>
        @endif
    </div>
</div>