@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mx-8">
                <div class="card-body">
                    
                    <h2>{{ array_get($form, 'name') }}</h2>
                    <p>{!! array_get($form, 'description') !!}</p>

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group {{$errors->has('contact_id') ? 'has-danger':''}}">
                                {{ Form::label('search', __('Search Name or Email')) }}
                                @if ($errors->has('contact_id'))
                                <span class="help-block text-danger">
                                    <small><strong>{{ $errors->first('contact_id') }}</strong></small>
                                </span>
                                @endif
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    {{ Form::text('search', null, ['class' => 'form-control', 'id' => 'autocomplete', 'placeholder' => __('Name or Email'), 'autocomplete' => 'off', 'required' => true]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('blank', __('&nbsp;')) }}
                                <br>
                                @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('join.create'), 'caption' => 'Add My Info', 'form' => true, 'size' => '', 'background' => 'btn-info'])
                            </div>

                        </div>
                    </div>

                    
                    {{ Form::open(['route' => ['pledges.submit', array_get($form, 'uuid')]]) }}
                    {{ Form::hidden('contact_id') }}
                    {{ Form::hidden('start_url', request()->fullUrl()) }}
                    
                    <div class="form-group">
                        {{ Form::label('amount', __('Amount')) }}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            {{ Form::number('amount', 1, ['class' => 'form-control', 'min' => '0', 'placeholder' => __('Amount'), 'step' => '0.01']) }}
                        </div>
                    </div>

                    <div class="form-group">
                        {{ Form::radio('is_recurring', 0, true) }} @lang('One Time')
                    </div>
                    <div class="form-group">
                        {{ Form::radio('is_recurring', 1) }} @lang('Recurring')
                    </div>

                    <div class="form-group one-time">
                        {{ Form::label('promised_pay_date', __('Promised pay date')) }}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {{ Form::text('promised_pay_date', \Carbon\Carbon::now()->toDateString(), ['class' => 'form-control datepicker', 'required' => true]) }}
                        </div>
                    </div>

                    <div class="row recurring">
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('billing_cycles', __('Frequency')) }}
                                {{ Form::number('billing_cycles', 1, ['class' => 'form-control', 'min' => 1]) }}
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <p>&nbsp;</p>
                            <p>@lang('Payments')</p>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('billing_period', __('&nbsp;')) }}
                                {{ Form::select('billing_period', ['Monthly' => 'Monthly', 'Weekly' => 'Weekly', 'Bi-Weekly' => 'Bi-Weekly'], null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group recurring">
                        {{ Form::label('billing_start_date', __('Start Billing at')) }}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {{ Form::text('billing_start_date', \Carbon\Carbon::now()->toDateString(), ['class' => 'form-control datepicker', 'required' => true]) }}
                        </div>
                    </div>

                    <div class="form-group recurring">
                        {{ Form::label('billing_end_date', __('End Billing at')) }}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {{ Form::text('billing_end_date', \Carbon\Carbon::now()->toDateString(), ['class' => 'form-control datepicker', 'required' => true]) }}
                        </div>
                    </div>

                    <div class="form-group">
                        {{ Form::label('comment', __('Comments')) }}
                        {{ Form::textarea('comment', null, ['class' => 'form-control']) }}
                    </div>
                    @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => '', 'caption' => 'Make a Pledge', 'form' => false, 'size' => '', 'background' => 'btn-primary'])
                    
                    {{ Form::close() }}
                </div>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .ui-datepicker{ z-index: 9999 !important; }
</style>
@endpush

@push('scripts')
<script class="text">
    (function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var url = "{{ route('public.contacts.autocomplete') }}";
        $('#autocomplete').autocomplete({
            source: function( request, response ) {
                // Fetch data
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $('input[name=contact_id]').val(ui.item.id);
            }
        });
        $('#autocomplete').on('keydown', function (e) {
            $('input[name=contact_id]').val('null');
        });

        $('input[name="is_recurring"]').on('click', function (e) {
            var value = parseInt($(this).val());
            if (value === 0) {
                $('.recurring').hide();
                $('.one-time').show();
            } else {
                $('.one-time').hide();
                $('.recurring').show();
            }
        });
        $('input[name="is_recurring"]:first').click();

        $('.recurring').hide();

        $('#billing_start_date').on('change', function (e) {
            var data = {
                'frequency': $('#billing_period').val(),
                'cycles': $('#billing_cycles').val(),
                'start': $('#billing_start_date').val()
            };

            $.post("{{ route('transactions.calendar.calculate.end.date') }}", data).done(function (data) {
                $('#billing_end_date').val(data.end);
            }).fail(function (data) {
                Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            });
        });
    })();
</script>
@endpush

@endsection
