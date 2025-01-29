<h4 class="pl-3">@lang('Permissions')</h4>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>@lang('Permission')</th>
            <th>@lang('Description')</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($permissionGroups as $group => $permissions)
            <tr>
                <td><strong>{{ $group }}<strong></td>
                <td></td>
                @if(request()->routeIs('roles.show'))
                <td></td>
                @else
                <td><input type="checkbox" data-check-all-permissions="true" data-group="{{ $group }}" /></td>
                @endif
            </tr>
            @foreach($permissions as $permission)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $permission->display_name }}</td>
                <td>{{ $permission->description }}</td>
                @if(request()->routeIs('roles.show'))
                <td>
                    @if(in_array($permission->id, $attached))
                    <i class="fa fa-check-square-o text-success fa-lg"></i>
                    @endif
                </td>
                
                @else
                <td data-group="{{ $group }}">
                    {{ Form::checkbox('permissions[]', $permission->id, in_array($permission->id, $attached)) }}
                </td>
                @endif
            </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    $('[data-check-all-permissions="true"]').click(function () {
        let checked = $(this).prop('checked');
        let group = $(this).attr('data-group');
        $('td[data-group="'+group+'"] input').prop('checked', checked);
    });
</script>
@endpush