@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('recurring.index') !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Recurring Transactions')</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'amount', 'order' => $nextOrder]) }}">
                                @lang('Amount')
                                @if( $sort === 'amount' )
                                    @if( isset($nextOrder) && $nextOrder === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($nextOrder) && $nextOrder === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'for', 'order' => $nextOrder]) }}">
                                @lang('Purpose')
                                @if( $sort === 'for' )
                                    @if( isset($order) && $order === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($order) && $order === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'campaign', 'order' => $nextOrder]) }}">
                                @lang('Fundraiser')
                                @if( $sort === 'campaign' )
                                    @if( isset($order) && $order === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($order) && $order === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'contact', 'order' => $nextOrder]) }}">
                                @lang('Contact')
                                @if( $sort === 'contact' )
                                    @if( isset($order) && $order === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($order) && $order === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'billing_period', 'order' => $nextOrder]) }}">
                                @lang('Billing Period')
                                @if( $sort === 'billing_period' )
                                    @if( isset($order) && $order === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($order) && $order === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>
                            @lang('Billing Frequency')
                        </th>
                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'billing_end_date', 'order' => $nextOrder]) }}">
                                @lang('End Date')
                                @if( $sort === 'billing_end_date' )
                                    @if( isset($order) && $order === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($order) && $order === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>

                        {{-- <th>
                            @lang('Type')
                            <!--
                            <a href="{{ route('recurring.index', ['sort' => 'status', 'order' => $nextOrder]) }}">
                                @lang('status')
                                @if( $sort === 'status' )
                                    @if( isset($nextOrder) && $nextOrder === 'desc' )
                                    <i class="fa fa fa-caret-up"></i>
                                    @else
                                    <i class="fa fa-sort-alpha-asc"></i>
                                    @endif
                                @endif
                            </a>
                            -->
                        </th> --}}

                        <th>
                            <a href="{{ route('recurring.index', ['sort' => 'status', 'order' => $nextOrder]) }}">
                                @lang('status')
                                @if( $sort === 'status' )
                                    @if( isset($nextOrder) && $nextOrder === 'desc' )
                                        <i class="fa fa-caret-up"></i>
                                    @elseif( isset($nextOrder) && $nextOrder === 'asc' )
                                        <i class="fa fa-caret-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>

                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($collection as $item)

                        <tr class="clickable-row" data-href="{{ route('recurring.show', ['id' => array_get($item, 'id'), 'tts' => array_get($item, 'transaction_template_split_id')]) }}">
                            <td>
                                <span class="badge badge-pill badge-primary p-2">$ {{ array_get($item, 'amount') }}</span>
                            </td>
                            <td>
                                <small>{{ array_get($item, 'chart_of_account_name') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($item, 'campaign_name') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($item, 'first_name') }} {{ array_get($item, 'last_name') }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ array_get($item, 'billing_period') }}ly
                                </small>
                            </td>
                            <td>
                                <small>{{ $item['billing_frequency'].' '.$item['billing_period']}}{{$item['billing_frequency'] > 1 ?'s':'' }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ ($billing_end_date = array_get($item, 'billing_end_date'))
                                    ? humanReadableDate($billing_end_date)
                                    : '--' }}
                                </small>
                            </td>
                            {{-- <td>
                                <span class="badge badge-info p-2">@lang('Recurring')</span>
                            </td> --}}
                            <td>
                                @includeIf('recurring_transactions.includes.status-color-indicator')
                            </td>
                            <td><span class="icon icon-arrow-right"></span></td>
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

@endsection
