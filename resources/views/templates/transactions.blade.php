@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-footer">
                @include('widgets.back')
            </div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('purposes.index') }}">@lang('Purposes')</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('purposes.show', ['id' => array_get($chart, 'id')]) }}">{{ array_get($chart, 'name') }}</a>
                </li>
                <li class="breadcrumb-item active">@lang('Transactions')</li>
            </ol>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Transactions')</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'amount', 'order' => $nextOrder]) }}">
                                @lang('Amount')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'amount' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'amount' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'for', 'order' => $nextOrder]) }}">
                                @lang('For')
                                @if( isset($order) && $order === 'asc' && $sort === 'for' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'contact', 'order' => $nextOrder]) }}">
                                @lang('Contact')
                                @if( isset($order) && $order === 'asc' && $sort === 'contact' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'contact' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'type', 'order' => $nextOrder]) }}">
                                @lang('Type')
                                @if( isset($order) && $order === 'asc' && $sort === 'type' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'type' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'date', 'order' => $nextOrder]) }}">
                                @lang('Last Transaction')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'date' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'date' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'status', 'order' => $nextOrder]) }}">
                                @lang('status')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'status' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'status' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('purposes.transactions', ['id' => array_get($chart, 'id'), 'sort' => 'card', 'order' => $nextOrder]) }}">
                                @lang('Payment option')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'card' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'card' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>&nbsp;</th>
                        <!--
                        <th>&nbsp;</th>
                        -->
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($splits as $split)
                        <tr class="clickable-row" data-href="{{ route('transactions.show', ['id' => array_get($split, 'id'), 'action' => 'chart_transactions']) }}">
                            <td>
                                <span class="badge badge-pill badge-primary p-2">$ {{ $split->amount }}</span>
                            </td>
                            <td>
                                <small>{{ $split->givingFor() }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'transaction.contact.first_name') }} {{ array_get($split, 'transaction.contact.last_name') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'type') }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'transaction.transaction_last_updated_at') }}</small>
                            </td>
                            <td class="text-center">
                                @include('transactions.includes.status-color-indicator')
                            </td>
                            <td class="text-center">
                                {{ array_get($split, 'transaction.paymentOption.card_type') }}
                            </td>
                            <td class="text-right">
                                <span class="icon icon-arrow-right btn"></span>
                            </td>
                        <!--
                        <td>
                            <a class="btn btn-link" href="{{ route('transactions.edit', ['id' => array_get($split, 'id')]) }}">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                        <td>
                            {{ Form::open( ['route' => ['transactions.destroy', array_get($split, 'id')], 'method' => 'DELETE', 'id' => 'form-'.array_get($split, 'id')] )  }}
                        {{ Form::hidden('uid', Crypt::encrypt(array_get($split, 'id'))) }}
                        {{ Form::close() }}
                                <button type="button" class="btn btn-link delete" data-form="#form-{{ array_get($split, 'id') }}" data-toggle="modal" data-target="#delete-modal">
                                <i class="fa fa-trash text-danger"></i>
                            </button>
                        </td>
                        -->
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                @if(count($splits) > 0)
                @if( isset($search) && $search === 'range' )
                @if( isset($sort) )
                {{ $splits->appends(['sort' => $sort, 'order' => $order, 'min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
                @else
                {{ $splits->appends(['min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
                @endif
                @elseif( isset($search) && $search === 'contact' )
                @if( isset($sort) )
                {{ $splits->appends(['sort' => $sort, 'order' => $order, 'keyword' => app('request')->input('keyword')])->links() }}
                @else
                {{ $splits->appends(['keyword' => app('request')->input('keyword')])->links() }}
                @endif
                @elseif( isset($search) && $search === 'status' )
                @if( isset($sort) )
                {{ $splits->appends(['sort' => $sort, 'order' => $order, 'status' => app('request')->input('status')])->links() }}
                @else
                {{ $splits->appends(['status' => app('request')->input('status')])->links() }}
                @endif
                @elseif( isset($sort) )
                {{ $splits->appends(['sort' => $sort, 'order' => $order])->links() }}
                @else
                {{ $splits->links() }}
                @endif
                @endif

            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@endsection
