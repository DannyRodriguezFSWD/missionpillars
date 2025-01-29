@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['forms.export.entries', array_get($form, 'id')], 'method' => 'GET', 'target' => '_blank']) }}
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::radio('export', 1, true) }} @lang('All')
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::radio('export', 0) }} @lang('Date')
                </div>
            </div>
            <div class="col-sm-8 text-right pb-2">
                <div class="" id="floating-buttons">
                    <button id="btn-submit-contact" type="submit" class="btn btn-primary">
                        <i class="fa fa-download"></i> @lang('Download')
                    </button>
                </div>
            </div>
        </div>
        <div class="row date-range">
            <div class="col-sm-3">
                {{ Form::label('from', __('From')) }}
                {{ Form::text('from', $from, ['class' => 'form-control datepicker', 'disabled' => true]) }}
            </div>
            <div class="col-sm-3">
                {{ Form::label('to', __('To')) }}
                {{ Form::text('to', $to, ['class' => 'form-control datepicker', 'disabled' => true]) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
    <div class="card-body">
        <h3>{{ array_get($form, 'name') }}</h3>
    </div>
    <div class="card-body" style="overflow-x: auto;">
        @include('forms.includes.table')
    </div>
    @if($entries instanceof \Illuminate\Pagination\LengthAwarePaginator )

        <div class="card-body">
            {{ $entries->links() }}
        </div>

    @endif
    
</div>

@push('scripts')
<script type="text/javascript">
    (function(){
        $('.date-range').hide();
        
        $('input[name="export"]').on('click', function(e){
            var value = $(this).prop('value');
            if(value === '1'){
                $('.date-range').fadeOut();
            }
            else{
                $('.date-range').fadeIn();
                $('input[name="from"]').prop('disabled', false);
                $('input[name="to"]').prop('disabled', false);
            }
        });
    })();
</script>
@endpush

@endsection
