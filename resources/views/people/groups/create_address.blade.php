@extends('layouts.app')

@section('content')


    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('widgets.back')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::open(['route' => 'addresses.store']) }}
                            {{ Form::hidden('rid', Crypt::encrypt($group->id)) }}
                            {{ Form::hidden('rtype', Crypt::encrypt(get_class($group))) }}
                            <div class="row">
                                <div class="col-md-6">
                                    <h1 class="">
                                        @if( isset($address) )
                                        @lang('Edit Address')
                                        @else
                                        @lang('Add New Address')
                                        @endif
                                    </h1>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> @lang('Save')</button>
                                </div>
                            </div>
                            @include('addresses.includes.address-info')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>

        </div>
        <!--/.col-->

    </div>
    <!--/.row-->

@push('scripts')
<script type="text/javascript">
    (function () {
        $('.datepicker').datepicker({
            format: "mm/dd/yyyy"
        });
        $('.date .input-group-addon').on('click', function (e) {
            $('.datepicker').focus();
        });


        var top = 35;
        $(window).scroll(function () {
            var y = $(this).scrollTop();
            var button = $('#btn-submit-contact');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '51px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
    })();
</script>
@endpush
@endsection
