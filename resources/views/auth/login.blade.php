@extends('layouts.auth-forms')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-group">
                    <div class="card p-sm-4">
                        <div class="card-body">
                            <div id="crm-login">
                                <crm-login
                                        v-bind:organization="'{{ array_get($tenant, 'organization') }}'"
                                        v-bind:subdomain="'{{ array_get($tenant, 'subdomain') }}'">
                                </crm-login>
                            </div>
                        </div>
                        <a class="btn btn-block btn-primary d-md-block d-lg-none d-xl-none" href="{{route('register')}}" type="button">Register Now!</a>
                    </div>
                    <div class="card text-white bg-primary py-5 d-md-down-none">
                        <div class="card-body text-center d-table">
                            <div class="d-table-cell align-middle">
                                <h2>@lang('Join') {{ array_get($tenant, 'organization') }}</h2>
                                @if (false)
                                <div class="card-body text-left">
                                    <ol>
                                        <li>Community Builder</li>
                                        <li>Donor Management</li>
                                        <li>Events and Tickets</li>
                                        <li>Child Check in</li>
                                        <li>Online Giving</li>
                                        <li>ChMS</li>
                                    </ol>
                                </div>
                                @endif
                                <a class="btn btn-lg btn-outline-light mt-3" href="{{route('register')}}" type="button">Register Now!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/crm-login.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
