@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('users.index') !!}
@endsection
@section('content')


<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <input type="text" class="form-control" placeholder="Name or email" id="searchUsers">
        </div>
    </div>
    
    <div class="col-md-6">
        @can('create', \App\Models\User::class)
        <a href="{{ route('users.create') }}" class="btn btn-success pull-right">
            <i class="fa fa-plus"></i> @lang('Add New User')
        </a>
        @endcan  
    </div>
</div>

<div class="row">
     <div class="col-12">
        <div class="card">
            <div class="card-header">
                <span class="h4">
                    <span id="userCount">{{ $total }}</span> @lang('Users')
                    @if ($searchedRole) 
                        with a role of '{{$searchedRole->display_name}}' 
                        <a class="small" href="{{route('users.index')}}"><em>Show users in all roles</em></a>
                    @endif
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="usersTable">
                    <table class="table table-hover clickable-table">
                        <thead>
                        <th>
                            <a href="{{ route('users.index', ['sort' => 'firstname', 'order' => $nextOrder]) }}">
                                @lang('Firstname')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'firstname' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'firstname' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('users.index', ['sort' => 'lastname', 'order' => $nextOrder]) }}">
                                @lang('Lastname')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'lastname' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'lastname' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('users.index', ['sort' => 'email', 'order' => $nextOrder]) }}">
                                @lang('Email')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'email' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'email' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @lang('Role')
                        </th>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr 
                            @can('update',$user)
                            data-href="{{ route('users.edit', ['id'=>$user->id]) }}"
                            @endcan
                            >
                                <td>
                                    {{ $user->name }}
                                </td>
                                <td>
                                    {{ $user->last_name }}
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    @if (count($user->roles))
                                        <a href="{{route('users.index')}}?roleid={{$user->roles[0]->id}}">
                                            {{ $user->roles[0]->display_name }}
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-body">
                @if( isset($nextOrder) && isset($sort) )
                {{ $users->appends(['sort' => $sort, 'order' => $order])->links() }}
                @else
                {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="application/javascript">
        $('#searchUsers').keyup(customDelay(function () {
            let search = $(this).val();

            customAjax({
                url: "{{ route('users.autocomplete') }}",
                data: {
                    search: search,
                    searchRole: @if ($searchedRole) {{ $searchedRole->id }} @else null @endif
                },
                success: function (data) {
                    $('#usersTable tbody').html('');
                        
                    if (data.length > 0) {
                        for (let i=0;i<data.length;i++) {
                            let item = data[i];

                            let tr = '<tr '
                                    + (item.can_update ? 'data-href="users/'+item.id+'/edit"' : '')
                                    + '>'
                                    + '<td>'+item.name+'</a></td>'
                                    + '<td>'+item.last_name+'</td>'
                                    + '<td>'+item.email+'</td>'
                                    + '<td>'
                                    + (item.role_id ? '<a href="{{route("users.index")}}?roleid='+item.role_id+'">'+item.role_name+'</a>' : '')
                                    + '</td>'
                                    + '</tr>';

                            $('#usersTable tbody').append(tr);
                        }
                    } else {
                        let tr = '<tr><td colspan="4"><span class="alert alert-info d-block">No users matched your search</span></td>'
                        $('#usersTable tbody').append(tr);
                    }
                        
                    $('#userCount').html(data.length);
                }
            });
        }, 500));
    </script>
@endpush

@endsection
