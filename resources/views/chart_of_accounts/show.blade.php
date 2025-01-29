@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('purposes.show',$chart) !!}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <div class="btn-group btn-group" role="group" aria-label="...">
                    @if(auth()->user()->can('transaction-view'))
                    <a href="{{ route('purposes.transactions', ['id' => $chart->id]) }}" class="btn btn-primary">
                        <i class="fa fa-dollar"></i>
                        @lang('View all transactions')
                    </a>
                    @endif
                    @if(!is_null($chart->tagInstance) && auth()->user()->can('contact-view'))
                    <a href="{{ route('tags.contacts', ['id' => $chart->tagInstance->id, 'action' => 'show_chart']) }}" class="btn btn-primary">
                        <i class="icon icon-user"></i>
                        @lang('View all donors')
                    </a>
                    @endif
                    @if(!is_null($chart->tenant_id) && auth()->user()->can('purposes-update'))
                    <a href="{{ route('purposes.edit', ['id' => $chart->id, 'action' => 'show']) }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i>
                        @lang('Edit')
                    </a>
                    @endif
                    <!--
                    @if(!is_null($chart->tenant_id))
                    {{ Form::open(['route' => ['purposes.destroy', $chart->id], 'method' => 'DELETE', 'id' => 'form-'.$chart->id]) }}
                    {{ Form::hidden('uid', Crypt::encrypt($chart->id)) }}
                    <button type="button" class="btn btn-danger delete" data-toggle="modal" data-target="#delete-modal" data-name="{{ $chart->name }}" data-form="#form-{{ $chart->id }}">
                        <span class="fa fa-trash"></span>
                        @lang('Delete')
                    </button>
                    {{ Form::close() }}
                    @endif
                    -->
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td>
                                <strong>@lang('Name'): </strong>
                                {{ $chart->getParent ? $chart->getParent->name. ' /' : '' }}
                                {{ $chart->name }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Description'): </strong>
                                {{ $chart->description }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Type'): </strong>
                                {{ $chart->type }}

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Account / Fund: </strong>
                                {{ $chart->account ? $chart->account->name : 'No Account' }} /
                                {{ $chart->fund ? $chart->fund->name : 'No Fund'}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Page Type'): </strong>
                                {{ $chart->page_type }}

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Page Sub Type'): </strong>
                                {{ $chart->sub_type }}

                            </td>
                        </tr>
                        @if(!is_null(array_get($chart, 'goal')))
                            <tr>
                                <td>
                                    <strong>@lang('Goal'): </strong>
                                    {{ $chart->goal }}
                                </td>
                            </tr>
                        @endif
                        @if(!is_null(array_get($chart, 'goal_cycle')))
                            <tr>
                                <td>
                                    <strong>@lang('Goal Cycle'): </strong>
                                    {{ $chart->goal_cycle }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <strong>@lang('Status'): </strong>
                                @if ($chart->is_active)
                                <span class="badge badge-success">@lang('Active')</span>
                                @else
                                <span class="badge badge-danger">@lang('Inactive')</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>
<!--/.row-->
@include('chart_of_accounts.includes.delete-modal')
@endsection
