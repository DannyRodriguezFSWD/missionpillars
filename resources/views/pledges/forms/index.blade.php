@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledgeforms.index') !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <h4>{{ $total }}</h4>
                <p>@lang('Pledge Forms')</p>
                @if(auth()->user()->can('pledge-create'))
                    <div class="btn-group btn-group" role="group" aria-label="...">
                        <a href="{{ route('pledgeforms.create') }}" class="btn btn-primary">
                            <i class="icon icon-plus"></i> @lang('Add Pledge Form')
                        </a>
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'amount', 'order' => $nextOrder]) }}">
                                @lang('Name')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'amount' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'amount' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'for', 'order' => $nextOrder]) }}">
                                @lang('Purpose')
                                @if( isset($order) && $order === 'asc' && $sort === 'for' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'for', 'order' => $nextOrder]) }}">
                                @lang('Fundraiser')
                                @if( isset($order) && $order === 'asc' && $sort === 'for' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('pledges.index', ['sort' => 'form', 'order' => $nextOrder]) }}">
                                @lang('Form')
                                @if( isset($order) && $order === 'asc' && $sort === 'contact' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'contact' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($forms as $form)
                        <tr class="clickable-row" data-href="{{ route('pledgeforms.show', ['id' => array_get($form, 'id')]) }}">
                            <td>{{ array_get($form, 'name') }}</td>
                            <td>{{ array_get($form, 'purpose.name') }}</td>
                            <td>{{ array_get($form, 'campaign.name') }}</td>
                            <td>{{ array_get($form, 'form.name') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@include('pledges.includes.search-contact')


@endsection
