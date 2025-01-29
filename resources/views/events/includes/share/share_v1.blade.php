@extends('layouts.auth-forms')
@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">

            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">{{ array_get($event, 'name') }}</h2>
                    {{ Form::open(['route' => ['events.signin', array_get($split, 'uuid')]]) }}
                    {{ Form::hidden('id', 'null') }}
                    {{ Form::hidden('tid', Crypt::encrypt(array_get($tenant, 'id'))) }}
                    {{ Form::hidden('eid', array_get($split, 'uuid')) }}
                    {{ Form::hidden('registry_id', array_get($register, 'id')) }}

                    <div class="input-group">
                        <input type="text" name="search" id="autocomplete" class="form-control form-control-lg" placeholder="@lang('Search Name or Email')" autocomplete="off">

                        <span class="input-group-append">
                            @if(array_get($event, 'allow_auto_check_in') && !array_get($event, 'allow_reserve_tickets'))
                                @php $caption = 'Check in'; @endphp
                            @else
                                @php $caption = 'Register now'; @endphp
                            @endif
                            @include('shared.sessions.submit-button', ['size' => 'btn-lg','notFlat' => true,'start_url' => request()->fullUrl(), 'next_url' => route('events.share', ['id' => array_get($split, 'uuid')]), 'caption' => $caption, 'form' => false])

                        </span>
                    </div>
                    {{ Form::close() }}
                    <div class="d-md-flex align-items-baseline justify-content-between mt-2">
                        @include('shared.sessions.submit-button', ['form_css' => 'd-inline','start_url' => request()->fullUrl(), 'next_url' => route('join.create',['registry' => request()->registry]), 'caption' => '<span class="text-dark font-weight-normal">Can\'t find yourself?</span> Sign Up!', 'form' => true, 'background' => 'btn-link font-weight-bold pl-0'])



                        <div class="mt-4 mt-md-0">
                            @include('shared.sessions.start-over-button',['class' => 'btn btn-link btn-lg', 'style' => ''])
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@push('scripts')
<script class="text">
    (function () {
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
                $('input[name=id]').val(ui.item.id);
            }
        });
        $('#autocomplete').on('keydown', function (e) {
            $('input[name=id]').val('null');
        });
    })();
</script>
@endpush

@endsection