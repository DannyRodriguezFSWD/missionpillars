@extends('layouts.app')
{{-- This view handles creating transaction (splits) AND pledges --}}
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledges.create') !!}
@endsection
@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                @if( $create_pledge === 'true' )
                {{ Form::open(['route' => 'pledges.store', 'id' => 'form']) }}
                {{ Form::hidden('pledge_id', array_get($split, 'id')) }}
                @else
                {{ Form::open(['route' => 'transactions.store', 'id' => 'form']) }}
                @endif
                {{ Form::hidden('create_pledge', $create_pledge) }}
                {{ Form::hidden('master_id', array_get($master, 'id')) }}
                
                <div class="row">
                    <div class="col-md-10">
                        @if($create_pledge === 'true')
                        <h1 class="">@lang('Create New Pledge')</h1>
                        @else
                        <h1 class="">@lang('Create New Transaction')</h1>
                        @endif
                    </div>
                    <div class="col-md-2 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                                <i class="icons icon-note"></i> @lang('Save')
                            </button>
                        </div>
                    </div>
                </div>

                @include('transactions.includes.form')

                {{ Form::close() }}
            </div>

        </div>

    </div>

</div>


@endsection
