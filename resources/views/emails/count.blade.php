@extends('layouts.app')

@section('content')


<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['emails.count', array_get($email, 'id'), 'list='.array_get($list, 'id')], 'id' => 'form', 'files' => true]) }}
        {{ Form::hidden('uid', Crypt::encrypt(array_get($list, 'id'))) }}
        
        <div class="row">
            <div class="col-md-9">
                <h3>@lang('Step 2')</h3>
            </div>
            <div class="col-md-3 text-right">
                <div class="btn-group btn-group" id="btn-submit-contact">
                    <a href="{{ route('emails.edit', ['id' => array_get($email, 'id')]) }}" class="btn btn-secondary">
                        <i class="icons icon-arrow-left"></i>
                        @lang('Previous')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        @lang('Next')
                        <i class="icons icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-sm-12" style="padding-top: 10px;">
                {{ Form::checkbox('do_not_send_to_previous_receivers', true, array_get($email, 'do_not_send_to_previous_receivers', false)) }} {{ Form::label('do_not_send_to_previous_receivers', __('Do not send to contacts who have already received this email')) }}
            </div>
        </div>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-sm-12">
                <h5>@lang('How many people from the list should we send this particular email to')?</h5>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-1" style="padding-top: 10px;">
                {{ Form::checkbox('send_to_all', true, array_get($email, 'send_to_all', false)) }} {{ Form::label('all', __('All or ')) }}
            </div>
            <div class="col-sm-2">
                <div class="form-group {{ $errors->has('send_number_of_emails') ? 'has-danger':'' }}">
                    {{ Form::number('send_number_of_emails', array_get($email, 'send_number_of_emails', 0), ['class' => 'form-control text-center', 'min' => 0]) }}
                </div>
            </div>
            <div class="col-sm-1" style="padding-top: 10px;">@lang('contacts')</div>
        </div>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        
        <div class="row">
            <div class="col-md-12">
                <h5>@lang('Number of days since the last time receiving an email within this list')</h5>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group {{ $errors->has('do_not_send_within_number_of_days') ? 'has-danger':'' }}">
                    {{ Form::number('do_not_send_within_number_of_days', array_get($email, 'do_not_send_within_number_of_days', 5), ['class' => 'form-control text-center', 'min' => 0]) }}
                </div>
            </div>
            <div class="col-sm-1" style="padding-top: 10px;">
                @lang('days')
            </div>
        </div>
        
        {{ Form::close() }}

    </div>

    <div class="card-footer">&nbsp;</div>
</div>

@push('scripts')
<script type="text/javascript">
    (function(){
        $('input[name="send_to_all"]').on('click', function(e){
            var checked = $(this).prop('checked');
            if(checked){
                $('input[name="send_number_of_emails"]').val(0);
            }
        });
    })();
</script>
@endpush

@endsection
