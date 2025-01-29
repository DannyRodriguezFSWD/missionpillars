@extends('layouts.app')
{{-- This view handles editing transaction (splits) AND pledges --}}
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledges.edit',$split) !!}
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
                {{ Form::model($split, ['route' => ['pledges.update', $split->id], 'id' => 'form', 'method' => 'PUT']) }}
                @else
                {{ Form::model($split, ['route' => ['transactions.update', $split->id], 'id' => 'form', 'method' => 'PUT']) }}
                @endif
                {{ Form::hidden('uid', Crypt::encrypt($split->id)) }}
                {{ Form::hidden('action', $action) }}
                {{ Form::hidden('transaction_payment_option_id', array_get($split, 'transaction.payment_option_id')) }}
                
                @if( isset($update_recurring) && !is_null($update_recurring) )
                {{ Form::hidden('update_recurring', $update_recurring) }}
                @endif
                
                {{ Form::hidden('create_pledge', $create_pledge) }}
                
                <div class="row">
                    <div class="col-md-10">
                        @if($create_pledge === 'true')
                        <h1 class="">@lang('Edit Pledge')</h1>
                        @else
                        <h1 class="">@lang('Edit Transaction')</h1>
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

@if(!is_null($split))
    @push('scripts')
    <script type="text/javascript">
        (function(){
            $('select[name="payment_option_id"]').val({{ array_get($split, 'transaction.paymentOption.id', -1) }});
            $('select[name="category"]').change();
            
        })();
    </script>
    @endpush
@endif

@endsection
