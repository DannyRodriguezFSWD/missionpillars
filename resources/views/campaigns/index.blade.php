@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('campaigns.index') !!}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Fundraisers')</p>
                <ul>
                    <li>@lang("All Fundraisers made in Continue To Give will auto populate here. So do not make them here as they will be duplicated").</li>
                    <li>@lang('All Fundraisers made here will NOT auto sync to Continue To Give')</li>
                </ul>
                <div role="group" aria-label="..." class="btn-group btn-group">
                    @can('create',\App\Models\Campaign::class)
                        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                            <i class="icon icon-user-follow"></i> @lang('Add New Fundraiser')
                        </a>
                    @endcan
                </div>
            </div>
            <table class="table table-hover table-responsive">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('campaigns.index', ['sort' => 'title', 'order' => $nextOrder]) }}">
                                @lang('Title')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'title' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'title' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('campaigns.index', ['sort' => 'type', 'order' => $nextOrder]) }}">
                                @lang('Type')
                                @if( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'type' )
                                    <i class="fa fa-caret-up"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'type' )
                                    <i class="fa fa-caret-down"></i>
                                @endif
                            </a>
                        </th>
                        <!--
                        <th>Goal</th>
                        <th>Goal Cycle</th>
                        <th>Tax Deductable</th>
                        -->
                        <!--
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        -->
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                    <tr 
                    @if(auth()->user()->can('campaign-view'))
                    class="clickable-row" data-href="{{ route('campaigns.show', ['id'=>$campaign->id]) }}"
                    @endif
                    >
                        <td>
                            {{ $campaign->getParent ? $campaign->getParent->name. ' /' : '' }}
                            {{ $campaign->name }}
                        </td>
                        <td>
                            {{ $campaign->page_type }}
                        </td>
                        <td class="text-right">
                                <span class="icon icon-arrow-right"></span>
                        </td>
                    </tr>

                    @endforeach
                </tbody>
            </table>
            <div class="card-body">
                @if( isset($nextOrder) && isset($sort) )
                {{ $campaigns->appends(['sort' => $sort, 'order' => $order])->links() }}
                @else
                {{ $campaigns->links() }}
                @endif
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@endsection
