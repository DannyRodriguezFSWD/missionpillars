@extends('layouts.auth-forms')

@section('content')
@php \App\Classes\Redirections::destroy() @endphp
<div class="col-lg-8 offset-lg-2">
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-sm-12">
                    <h1 class="font-weight-light text-center">@lang('Thank you for registering') {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</h1>
                    <p class=" text-center">@lang("You should be receiving an email with event details")</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-12 text-center">
                    <a href="{{ $redirect }}" class="btn btn-primary">
                        @lang('Go back')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style type="text/css">
    html, body{
        background: #ffffff;
    }
</style>
@endpush
@push('scripts')
    <script src="{{asset('js/confetti.browser.min.js')}}"></script>
    <script>
        simpleConfettiFireWorks();
    </script>
@endpush

@endsection
