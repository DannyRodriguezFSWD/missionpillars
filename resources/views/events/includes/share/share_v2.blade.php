<div class="card shadow-lg border-0">

    <div class="card-body p-0">
        <div class="row">
            <div class="col-md-6 pr-md-0">
                <div style="height: 100%; border-radius: 0" class="card bg-white border-bottom-0">
                    @if(!is_null( array_get($event, 'img_cover') ))
                        <img src="{{ asset('storage/event_images/'.array_get($event, 'img_cover')) }}" class="card-img-top d-md-none"/>
                    @endif
                    <div class="card-body">
                        <h1 class="card-title text-center">{{ array_get($event, 'name') }}</h1>
                        <h5 class="card-subtitle text-center">Organizer: {{ array_get($event, 'tenant.organization') }}</h5>
                        <div class="row mt-2">
                            <div class="col-sm-12">
                                <h5 class="text-uppercase">When and Where</h5>
                                <h6 class="text-uppercase">
                                    <i class="fa fa-clock-o"></i>
                                    {{ displayDateTimeRange(
                                        array_get($split, 'start_date'),
                                        array_get($split, 'end_date'),
                                        array_get($event, 'is_all_day'),
                                        array_get($event, 'timezone')
                                    ) }}
                                    @if ( ! array_get($event, 'is_all_day') && array_get($event, 'timezone') )
                                        <br/><span style="font-size:x-small">( Timezone: {{ array_get($event, 'timezone') }} ) </span>
                                    @endif

                                </h6>
                                @if(!is_null(array_get($event, 'addressInstance.0')))
                                    <div class="text-uppercase">
                                        <i class="fa fa-map-marker"></i>
                                        {{ array_get($event, 'addressInstance.0.mailing_address_1') }}
                                    </div>
                                    <div class="text-uppercase">

                                        {{ array_get($event, 'addressInstance.0.city') }}
                                    </div>
                                    <div class="text-uppercase">

                                        {{ array_get($event, 'addressInstance.0.region') }}
                                    </div>
                                    <div class="text-uppercase">

                                        {{ array_get($event, 'addressInstance.0.countries.name') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-12">
                                <hr>
                                <h5 class="text-uppercase">Event Details</h5>
                                <div>{!! array_get($event, 'description') !!}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 pl-md-0">
                <div style="height: 100%" class="card bg-white border-0">
                    @if(!is_null( array_get($event, 'img_cover') ))
                        <img src="{{ asset('storage/event_images/'.array_get($event, 'img_cover')) }}" class="card-img-top d-sm-down-none"/>
                    @endif
                    @if(!isset($event_ended))
                            <div class="card-body">
                                @if(array_get($event, 'allow_reserve_tickets')) <h3 class="card-title">Tickets</h3> @endif
                                {{ Form::open(['route' => ['events.purchase.tickets', array_get($split, 'id')], 'method' => 'POST']) }}
                                {{ Form::hidden('total') }}
                                {{ Form::hidden('contact_id', array_get($contact, 'id')) }}
                                {{ Form::hidden('register_id', array_get($register, 'id')) }}
                                {{ Form::hidden('ticket_id', $ticket) }}

                                @if(array_get($event, 'allow_reserve_tickets'))
                                    @include('events.includes.share.tickets')
                                @else
                                    <div class="text-right">
                                        @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('join.create'), 'caption' => 'Sign Up', 'form' => false, 'background' => 'btn-success'])
                                    </div>
                                @endif

                                {{ Form::close() }}
                            </div>
                    @else
                        <h2 class="text-center mt-md-4">This event has already ended.</h2>
                    @endif

                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            Use <a href="{{ route('dashboard.index') }}">Mission Pillars&copy;</a> for event management and online registration
        </div>
    </div>

</div>
{{--<div class="m-4 p-4"></div>--}}
{{--<footer class="app-footer">--}}
{{--    <div class="container">--}}
{{--        <div class="row">--}}
{{--            <div class="col-sm-12">--}}
{{--                Use <a href="{{ route('dashboard.index') }}">Mission Pillars&copy;</a> for event management and online registration--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</footer>--}}
