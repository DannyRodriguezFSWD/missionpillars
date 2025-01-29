@extends('layouts.events-share')

@section('content')

<div class="card shadow-lg border-0">
    <div class="card-body p-0">
        <div class="row">
            <div class="col-md-6 pr-md-0">
                <div style="height: 100%; border-radius: 0" class="card bg-white border-bottom-0">
                    @if(!is_null( array_get($group, 'cover_image') ))
                        <img src="{{ asset('storage/groups/'.array_get($group, 'cover_image')) }}" class="card-img-top d-md-none"/>
                    @endif
                    <div class="card-body">
                        <h1 class="card-title text-center">{{ array_get($group, 'name') }}</h1>
                        <h5 class="card-subtitle text-center">Organization: {{ array_get($group, 'tenant.organization') }}</h5>
                        <div class="row mt-2">
                            <div class="col-sm-12">
                                <h5 class="text-uppercase">Location and contact</h5>
                                @if(!is_null(array_get($group, 'manager')))
                                    <div class="text-uppercase">
                                        <i class="fa fa-user"></i>
                                        {{ array_get($group, 'manager.full_name') }}
                                    </div>
                                @endif
                                @if(!is_null(array_get($group, 'addressInstance.0')))
                                    <div class="text-uppercase">
                                        <i class="fa fa-map-marker"></i>
                                        {{ array_get($group, 'full_address') }}
                                    </div>
                                @endif
                                @if(!is_null(array_get($group, 'manager.cell_phone')))
                                    <div class="text-uppercase">
                                        <i class="fa fa-phone"></i>
                                        {{ array_get($group, 'manager.cell_phone') }}
                                    </div>
                                @endif
                                @if(!is_null(array_get($group, 'manager.email_1')))
                                    <div>
                                        <i class="fa fa-envelope"></i>
                                        {{ array_get($group, 'manager.email_1') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-12">
                                <hr>
                                @if ($group->description)
                                <h5 class="text-uppercase">Group Details</h5>
                                <div>{!! array_get($group, 'description') !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 pl-md-0">
                <div style="height: 100%" class="card bg-white border-0">
                    @if(!is_null( array_get($group, 'cover_image') ))
                        <img src="{{ asset('storage/groups/'.array_get($group, 'cover_image')) }}" class="card-img-top d-sm-down-none"/>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 offset-md-3">
                {{ Form::open(['route' => ['join.join', $group->uuid]]) }}
                {{ Form::hidden('id', 'null') }}
                {{ Form::hidden('start_url', request()->fullUrl()) }}
                <p>&nbsp;</p>

                <div class="input-group input-group-lg">
                    <input type="text" name="search" id="autocomplete" class="form-control" placeholder="@lang('Search Name or Email')" autocomplete="off">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-info btn-lg" id="clear">
                            <i class="icon icon-paper-plane"></i> @lang('Join Group')
                        </button>
                    </span>
                </div>
                {{ Form::close() }}
                <br/>
                <div class="text-center">
                    <h5>@lang("Can't find yourself?")</h5>
                    @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('join.create'), 'caption' => 'Sign Up', 'form' => true, 'background' => 'btn-link'])
                </div>
            </div>
        </div>
        
        <div class="card-footer text-right">
            Powered by <a href="{{ route('dashboard.index') }}">Mission Pillars&copy;</a>
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
        $('#autocomplete').on('keydown', function(e){
            if(e.which != 13){
                $('input[name=id]').val('null');
            }
        });
    })();
    
    @if (session('message'))
        Swal.fire("{{ session('message') }}", '', 'success');
    @endif
    
</script>
@endpush

@endsection
