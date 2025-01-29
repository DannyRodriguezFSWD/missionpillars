@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledges.index') !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Pledges')</p>
                <div class="btn-group btn-group" role="group" aria-label="...">
                    @if(auth()->user()->can('pledge-create'))
                        <a href="{{ route('pledges.create') }}" class="btn btn-primary">
                            <i class="icon icon-plus"></i> @lang('Add Pledge')
                        </a>
                    @endif

                    <button class="btn btn-primary" data-toggle="modal" data-target="#search-contact-modal">
                        <i class="fa fa-search"></i>
                        @lang('Advanced Search')
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'amount', 'order' => $nextOrder]) }}">
                                @lang('Amount')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'amount' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'amount' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'for', 'order' => $nextOrder]) }}">
                                @lang('For')
                                @if( isset($order) && $order === 'asc' && $sort === 'for' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>

                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'campaign', 'order' => $nextOrder]) }}">
                                @lang('Fundraiser')
                                @if( isset($order) && $order === 'asc' && $sort === 'campaign' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'campaign' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>

                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'contact', 'order' => $nextOrder]) }}">
                                @lang('Contact')
                                @if( isset($order) && $order === 'asc' && $sort === 'contact' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'contact' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>

                        <th>
                        @lang('Type')
                        <!--
                            <a href="{{ route('pledges.index', ['sort' => 'status', 'order' => $nextOrder]) }}">
                                @lang('status')
                        @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'status' )
                            <i class="fa fa-sort-alpha-desc"></i>
@elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'status' )
                            <i class="fa fa-sort-alpha-asc"></i>
@endif
                                </a>
-->
                        </th>

                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'status', 'order' => $nextOrder]) }}">
                                @lang('status')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'status' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'status' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($collection as $item)
                        @php $template = App\Models\TransactionTemplate::findOrFail(array_get($item, 'id')) @endphp
                        <tr class="clickable-row" data-href="{{ route('pledges.show', ['id' => array_get($template, 'id')]) }}">
                            <td>
                                <span class="badge badge-pill badge-primary p-2">$ {{ array_get($template, 'splits.0.amount') }}</span>
                            </td>
                            <td>
                                @if(!is_null(array_get($template, 'splits.0')))
                                    <small>{{ $template->splits[0]->givingFor() }}</small>
                                @endif
                            </td>

                            <td>
                                <small>{{ array_get($template, 'splits.0.campaign.name') }}</small>
                            </td>

                            <td>
                                <small>{{ array_get($template, 'contact.first_name') }} {{ array_get($template, 'contact.last_name') }}</small>
                            </td>
                            <td>
                                @if( array_get($template, 'is_recurring') )
                                    <span class="badge badge-info p-2">@lang('Recurring')</span>
                                @else
                                    <span class="badge badge-default p-2">@lang('Single')</span>
                                @endif
                            </td>
                            <td>
                                @includeIf('pledges.includes.status-color-indicator')
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                @if(count($collection) > 0)
                    @if( isset($search) && $search === 'range' )
                        @if( isset($sort) )
                        {{ $collection->appends(['sort' => $sort, 'order' => $order, 'min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
                        @else
                        {{ $collection->appends(['min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
                        @endif
                    @elseif( isset($search) && $search === 'contact' )
                        @if( isset($sort) )
                        {{ $collection->appends(['sort' => $sort, 'order' => $order, 'keyword' => app('request')->input('keyword')])->links() }}
                        @else
                        {{ $collection->appends(['keyword' => app('request')->input('keyword')])->links() }}
                        @endif
                    @elseif( isset($search) && $search === 'status' )
                        @if( isset($sort) )
                        {{ $collection->appends(['sort' => $sort, 'order' => $order, 'status' => app('request')->input('status')])->links() }}
                        @else
                        {{ $collection->appends(['status' => app('request')->input('status')])->links() }}
                        @endif
                    @elseif( isset($sort) )
                    {{ $collection->appends(['sort' => $sort, 'order' => $order])->links() }}
                    @else
                    {{ $collection->links() }}
                    @endif
                @endif

            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@include('pledges.includes.search-contact')


@endsection
