@extends('layouts.public')

@section('content')

<div style="background-image: url({{ asset('img/personal-fundraising.jpg') }}); background-size: cover; height: 100vh;">
    <div class="row">
        <div class="col-lg-4 col-md-6 offset-lg-4 offset-md-3">
            <div class="card mt-5">
                <div class="card-header">
                    <h2 class="text-center">{{ array_get($tenant, 'organization') }}</h2>
                    <h2 class="text-center">@lang('Subscribe For Updates')</h2>
                </div>

                <div class="card-body">
                    {{ Form::open(['route' => 'newsletter.store', 'id' => 'newsletterForm']) }}

                    <div class="form-group">
                        <span class="text-danger">*</span>
                        {{ Form::label('first_name', __('First Name')) }}
                        {{ Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => __('First Name'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>

                    <div class="form-group">
                        <span class="text-danger">*</span>
                        {{ Form::label('last_name', __('Last Name')) }}
                        {{ Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('Last Name'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>

                    <div class="form-group">
                        <span class="text-danger">*</span>
                        {{ Form::label('email1', __('Email')) }}
                        {{ Form::email('email1', null, ['class' => 'form-control', 'placeholder' => __('Email'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>

                    <div class="form-group">
                        <div class="form-check-inline">
                            <label class="form-check-label">
                                <input name="phone_consent" type="checkbox" class="form-check-input" value="" id="phoneConsent"> Text me with news and updates
                            </label>
                        </div>
                    </div>
                    
                    <div id="phoneContainer" class="d-none">
                        <div class="form-group">
                            <span class="text-danger">*</span>
                            {{ Form::label('cell_phone', __('Phone Number')) }}
                            {{ Form::text('cell_phone', null, ['class' => 'form-control', 'placeholder' => __('Phone Number'), 'autocomplete' => 'off']) }}
                        </div>

                        <div class="alert alert-info">
                            By providing your phone number, you agree to receive text messages from {{ array_get($tenant, 'organization') }}. Message and data rates may apply. You may receive up to 10 messages per month.
                            <br>
                            Reply "STOP" to opt out or "HELP" for help. 
                        </div>
                    </div>
                    
                    <p>
                        By proceeding, you agree to the <a href="{{ route('newsletter.privacy') }}" target="_blank">Terms / Privacy</a>
                    </p>

                    <button class="btn btn-success mt-3" type="submit">
                        Subscribe
                    </button>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#phoneConsent').change(function () {
        if ($(this).prop('checked') === true) {
            $('#phoneContainer').removeClass('d-none');
            $('[name="cell_phone"]').prop('required', true);
        } else {
            $('#phoneContainer').addClass('d-none');
            $('[name="cell_phone"]').prop('required', false);
        }
    });
</script>
@endpush

@endsection
