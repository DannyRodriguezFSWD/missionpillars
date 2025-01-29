@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-header">
                    <h5>@lang('Email Subscription')</h5>
                </div>
                <div class="card-body">
                    @if(!empty($list) && !$contact->unsubscribed)
                        @if(!$list_unsubscribed)
                            {{ Form::open(['route' => ['emails.unsubscribe.contact']]) }}
                            {{ Form::hidden('list', array_get($list, 'id')) }}
                            {{ Form::hidden('email', array_get($email, 'id')) }}
                            {{ Form::hidden('contact', array_get($contact, 'id')) }}
                            {{ Form::hidden('sent', array_get($sent, 'id')) }}

                            <div class="row">
                                <div class="col-sm-12">
                                    <p>
                                        @lang('Do you want to unsubscribe') <strong>{{ array_get($contact, 'email_1') }}</strong> @lang('from this mailing list?')
                                    </p>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-exclamation-triangle"></i> @lang('Unsubscribe from this mailing list')
                                    </button>
                                </div>
                            </div>
                            {{ Form::close() }}
                        @else
                            {{ Form::open(['route' => ['emails.subscribe.contact']]) }}
                            {{ Form::hidden('list', array_get($list, 'id')) }}
                            {{ Form::hidden('email', array_get($email, 'id')) }}
                            {{ Form::hidden('contact', array_get($contact, 'id')) }}
                            {{ Form::hidden('sent', array_get($sent, 'id')) }}

                            <div class="row">
                                <div class="col-sm-12">
                                    <p>
                                        @lang('The email') <strong>{{ array_get($contact, 'email_1') }}</strong> @lang('is already unsubscribed from this mailing list.')
                                    </p>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-exclamation-triangle"></i> @lang('Subscribe to this mailing list')
                                    </button>
                                </div>
                            </div>
                            {{ Form::close() }}
                        @endif
                    @endif
                    @if($contact->unsubscribed)
                        <p>Subscribe <strong>{{ array_get($contact, 'email_1') }}</strong> from <b>{{array_get($contact,'tenant.organization')}}?</b></p>
                    @endif
                    @if(empty($list) && !$contact->unsubscribed)
                        <p>Unsubscribe <strong>{{ array_get($contact, 'email_1') }}</strong> from <b>{{array_get($contact,'tenant.organization')}}?</b></p>
                    @endif

                </div>

                <div class="card-footer">
                    @if($contact->unsubscribed)
                        {{ Form::open(['route' => ['emails.subscribe.contact'], 'id' => 'form_resub_from_all']) }}
                        {{ Form::hidden('list', array_get($list, 'id')) }}
                        {{ Form::hidden('email', array_get($email, 'id')) }}
                        {{ Form::hidden('contact', array_get($contact, 'id')) }}
                        {{ Form::hidden('sent', array_get($sent, 'id')) }}
                        {{ Form::hidden('from_all', true) }}
                        <button class="btn btn-block btn-success"><i class="fa fa-bell"></i>&nbsp;Subscribe <strong>{{ array_get($contact, 'email_1') }}</strong> to <b>{{array_get($contact,'tenant.organization')}}</b></button>
                        {{ Form::close() }}
                    @else
                        {{ Form::open(['route' => ['emails.unsubscribe.contact']]) }}
                        {{ Form::hidden('list', array_get($list, 'id')) }}
                        {{ Form::hidden('email', array_get($email, 'id')) }}
                        {{ Form::hidden('contact', array_get($contact, 'id')) }}
                        {{ Form::hidden('sent', array_get($sent, 'id')) }}
                        {{ Form::hidden('from_all', true) }}
                        <button class="btn btn-block btn-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp;Unsubscribe from all communications from <b>{{ array_get($contact,'tenant.organization') }}</b></button>
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@if(session('message'))
    @push('scripts')
        <script>
            Swal.fire({
                type: 'success',
                html: '<h3>{{session('message')}}</h3>&nbsp;<i class="fa fa-bell"></i>',
            })
        </script>
    @endpush
@endif
