<div class="col-md-12 promote_togglable_div" id="group-settings">
    <div class="alert alert-info">
        <h4 class="mb-0" id="settingsTitle">
            Then select the group of people that are going to checkin
        </h4>
    </div>
    
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title font-weight-bold inline_block mb-0">Group Settings</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">Limit search screen to a specific group or select all people.</div>
                    
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle" type="button" id="groupsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <b>Group:</b>
                            <i class="fa fa-filter"></i>
                            @lang ('Select Group')
                        </button>
                        <div class="dropdown-menu" aria-labelledby="groupsDropdown">
                            <a class="dropdown-item" href="#" data-name="@lang('All People')" data-id="0" onclick="filterContacts(this, 'group')">
                                @lang('All People')
                            </a>
                            <div class="dropdown-divider"></div>

                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="Search Group" id="searchGroups" autocomplete="off">
                            </div>

                            <div id="groupsContainer" class="overflow-auto" style="max-height: 300px;">
                                @foreach ($groups as $group)
                                <a class="dropdown-item" href="#" data-name="{{ array_get($group, 'name') }}" data-id="{{ array_get($group, 'uuid') }}" onclick="filterContacts(this, 'group')">
                                    {{ array_get($group, 'name') }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
