@extends('layouts.app')

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('js/calendars/spectrum.css') }}" type="text/css" />
@endpush

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    {{ Form::open(['route' => 'calendars.store', 'id' => 'calendar']) }}
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12 text-right pb-2">
                <div class="" id="floating-buttons">
                    <button type="submit" class="btn btn-primary">
                        <span class="fa fa-edit"></span>
                        @lang('Save')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <h4>@lang('Create New Calendar')</h4>
        <div class="form-group">
            <span class="text-danger">*</span> 
            {{ Form::label('name', __('Name')) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => "Calendar's name", 'required' => true, 'autocomplete' => 'off']) }}
        </div>

        <div class="form-group">
            {{ Form::label('color', __('Color')) }}
            {{ Form::text('color', null, ['class' => 'form-control', 'placeholder' => "Calendar's name", 'id' => 'custom']) }}

            <div id="error" class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                @lang("Calendar's color is required")
            </div>
        </div>
        <div class="form-group">
            {{ Form::checkbox('public', 1, array_get($calendar, 'public', true)) }}
            {{ Form::label('public', __('Make this calendar public')) }}
        </div>
    </div>
    <div class="card-footer">
        &nbsp;
    </div>
    {{ Form::close() }}
</div>

@push('scripts')
<script type="text/javascript" src="{{ asset('js/calendars/spectrum.js') }}"></script>
<script type="text/javascript">
(function () {
    $('#calendar').on('submit', function (e) {
        var value = $('#custom').val();
        if (value === null || value === '' || value === 'undefined') {
            $('#error').show();
            return false;
        }
    });

    $("#custom").spectrum({
        color: "#ECC",
        showInput: true,
        className: "full-spectrum",
        showInitial: true,
        showPalette: true,
        showSelectionPalette: true,
        maxSelectionSize: 10,
        preferredFormat: "hex",
        localStorageKey: "spectrum.demo",
        move: function (color) {

        },
        show: function () {

        },
        beforeShow: function () {

        },
        hide: function () {

        },
        change: function (color) {

        },
        palette: [
            ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)",
                "rgb(204, 204, 204)", "rgb(217, 217, 217)", "rgb(255, 255, 255)"],
            ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
            ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
        ]
    });

})();
</script>
@endpush

@endsection
