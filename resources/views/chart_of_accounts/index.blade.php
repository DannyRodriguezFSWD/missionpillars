@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('purposes.index') !!}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Purposes')</p>
                <ul>
                    <li>@lang("All Projects, Missionaries, and Missionary's projects made in Continue To Give will automatically populate here. So do not make them here as they will be duplicated").</li>
                    <li>@lang('All Purposes made here will NOT auto sync to Continue To Give')</li>
                </ul>
                @if(auth()->user()->can('purposes-create'))
                <div role="group" aria-label="..." class="btn-group btn-group">
                    <a href="{{ route('purposes.create') }}" class="btn btn-primary">
                        <i class="icon icon-user-follow"></i> @lang('Add New Purpose')
                    </a>
                </div>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ route('purposes.index', ['sort' => 'title', 'order' => $nextOrder]) }}">
                                @lang('Title')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'title' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'title' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @lang('Type')
                        </th>
                        <th>
                            Account / Fund
                        </th>
                        <th>@lang('Status')</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $missionaryRowAdded = false; @endphp
                    @foreach($charts as $chart)
                        @if ($chart->type === 'Organization')
                        <tr class="clickable-row" data-href="{{ route('purposes.show', ['id'=>$chart->id]) }}">
                            <th>
                                {{ $chart->getParent ? $chart->getParent->name. ' /' : '' }}
                                {{ $chart->name }}
                            </th>
                            <th>
                                {{ $chart->type }}
                            </th>
                            <td>

                                {{ $chart->account ? $chart->account->name : 'No Account' }} /
                                {{ $chart->fund ? $chart->fund->name : 'No Fund'}}
                            </td>
                            <td>
                                @if ($chart->is_active)
                                <span class="badge badge-success">@lang('Active')</span>
                                @else
                                <span class="badge badge-danger">@lang('Inactive')</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="icon icon-arrow-right"></span>
                            </td>
                        </tr>
                        @else
                        @if (!$missionaryRowAdded && $chart->type === 'Missionary')
                        <tr>
                            <th colspan="4">Missionaries</th>
                        </tr>
                        @php $missionaryRowAdded = true; @endphp
                        @endif
                        <tr class="clickable-row" data-href="{{ route('purposes.show', ['id'=>$chart->id]) }}">
                            <td @if($chart->type === 'Missionary') style="padding-left: 50px;" @endif>
                                {{ $chart->getParent ? $chart->getParent->name. ' /' : '' }}
                                {{ $chart->name }}
                            </td>
                            <td>
                                {{ $chart->type }}
                            </td>
                            <td>

                                {{ $chart->account ? $chart->account->name : 'No Account' }} /
                                {{ $chart->fund ? $chart->fund->name : 'No Fund'}}
                            </td>
                            <td>
                                @if ($chart->is_active)
                                <span class="badge badge-success">@lang('Active')</span>
                                @else
                                <span class="badge badge-danger">@lang('Inactive')</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="icon icon-arrow-right"></span>
                            </td>
                        </tr>
                        @endif

                        @foreach( $chart->getChildren as $subaccount )
                            <tr class="clickable-row" data-href="{{ route('purposes.show', ['id'=>$subaccount->id]) }}">
                                <td @if($chart->type === 'Missionary') style="padding-left: 100px;" @else style="padding-left: 50px;" @endif>
                                    {{-- $subaccount->getParent ? $subaccount->getParent->name. ' /' : '' --}}
                                    {{ $subaccount->name }}
                                </td>
                                <td>
                                    {{ $subaccount->type }}
                                </td>
                                <td>
                                    {{ $subaccount->account ? $subaccount->account->name : 'No Account' }} /
                                    {{ $subaccount->fund ? $subaccount->fund->name : 'No Fund'}}
                                </td>
                                <td>
                                    @if ($subaccount->is_active)
                                    <span class="badge badge-success">@lang('Active')</span>
                                    @else
                                    <span class="badge badge-danger">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                @if( isset($nextOrder) && isset($sort) )
                {{ $charts->appends(['sort' => $sort, 'order' => $order])->links() }}
                @else
                {{ $charts->links() }}
                @endif
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@endsection
