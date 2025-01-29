@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('campaigns.show',$campaign) !!}
@endsection
@section('content')
@php
@endphp
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
	    	@if ($from_c2g)
		<div class="row"><div class="col text-right">
		    View Only <div style="font-size: x-small">(Edit in Continue to Give)</div>
		</div></div>
		@endif
                <div class="btn-group btn-group" role="group" aria-label="...">
                    
                    <a href="{{ route('tags.contacts', ['id' => array_get($campaign, 'tagInstance.id'), 'action' => 'show_chart']) }}" class="btn btn-primary">
                        <i class="icon icon-user"></i>
                        @lang('View all donors')
                    </a>
		    @if (!$from_c2g)
                    @can('update',$campaign)
                        <a href="{{ route('campaigns.edit', ['id' => array_get($campaign, 'id'), 'action' => 'show']) }}" class="btn btn-primary">
                            <i class="fa fa-edit"></i>
                            @lang('Edit')
                        </a>
                    @endcan
		    @endif
                    <!--
                    {{ Form::open(['route' => ['campaigns.destroy', $campaign->id], 'method' => 'DELETE', 'id' => 'form-'.$campaign->id]) }}
                    {{ Form::hidden('uid', Crypt::encrypt($campaign->id)) }}
                    <button type="button" class="btn btn-danger delete" data-toggle="modal" data-target="#delete-modal" data-name="{{ $campaign->name }}" data-form="#form-{{ $campaign->id }}">
                        <span class="fa fa-trash"></span>
                        @lang('Delete')
                    </button>
                    {{ Form::close() }}
                    -->
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td>
                                <strong>@lang('Name'): </strong>
                                {{ !is_null( array_get($campaign, 'purpose.getParent') ) ? array_get($campaign, 'purpose.getParent.name').' / ' : '' }}
                                {{ !is_null( array_get($campaign, 'purpose') ) ? array_get($campaign, 'purpose.name').' / ' : '' }}

                                {{ array_get($campaign, 'name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Description'): </strong>
                                {{ array_get($campaign, 'description') }}
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong>@lang('Page Type'): </strong>
                                {{ array_get($campaign, 'page_type') }}

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Page Sub Type'): </strong>
                                {{ array_get($campaign, 'sub_type') }}

                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong>@lang('Goal'): </strong>
                                <span class="badge badge-pill badge-success p-2">
                                    ${{ number_format(array_get($campaign, 'goal', 0), 2) }}
                                </span>
                            </td>
                        </tr>
                        <!--
                        <tr>
                            <td>
                                <strong>@lang('Goal Cycle'): </strong>
                                {{ array_get($campaign, 'goal_cycle') }}
                                </td>
                            </tr>
-->
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>
<!--/.row-->
@include('campaigns.includes.delete-modal')
@endsection
