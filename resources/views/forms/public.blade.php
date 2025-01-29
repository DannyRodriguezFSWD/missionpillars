@extends('layouts.auth-forms')

@section('content')

    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-8 offset-md-2 p-0 m-0">
            <div class="card mb-0">
                <div class="card-body">
                    {{ Form::open(['route' => ['forms.submit', $form->uuid, $params], 'id' => 'form', 'files' => true]) }}
                    {{ Form::hidden('uid', Crypt::encrypt($form->uuid)) }}
                    {{ Form::hidden('cid', null) }}
                    <div class="row">
                        <div class="col-12">
                            @if(!array_get($form, 'do_not_show_form_name'))
                                <h2 class="card-title">{{ $form->name }}</h2>
                            @endif

                            @if(!is_null($form->cover))
                                <img src="{{ asset('storage/form_images/'.$form->cover) }}" class="img-responsive" style="max-height: 33vh; margin: 0 auto"/>
                            @endif
                            <div id="fb-rendered-form"></div>
                            
                            @if ($hasProfileImage)
                                <div id="profile-image-container" class="d-none">
                                    @include('_partials.cover-image', ['title' => 'Profile Picture', 'aspectRatio' => 1])
                                </div>
                            @endif                            
                        </div>
                    </div>
                    @if(array_get($form, 'accept_payments'))
                        <div class="row">
                            <div class="col-sm-12"><hr/></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 text-right">
                                <h3>@lang('Total')</h3>
                            </div>
                            <div class="col-sm-1 text-right">
                                <span class="h3">@lang('$')</span>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" name="total" value="0.00" readonly="" style="font-size: 1.75rem; width: 100%; font-weight: 500; margin-top: -7px; border: none; text-align: left;"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12"><hr/></div>
                        </div>
                    @endif

                    <div class="text-right">
                        @if(array_get($form, 'accept_payments'))
                            @include('shared.sessions.submit-button', ['size' => 'reg', 'start_url' => request()->fullUrl(), 'next_url' => route('forms.submit', ['id' => $form->uuid]), 'caption' => 'Submit and Pay', 'form' => false])
                        @else
                            @include('shared.sessions.submit-button', ['size' => 'reg', 'start_url' => request()->fullUrl(), 'next_url' => route('forms.submit', ['id' => $form->uuid]), 'caption' => 'Submit form', 'form' => false])
                        @endif
                    </div>
                    {{ Form::close() }}
                </div>
                @include('shared.sessions.start-over-button')
            </div>
        </div>
    </div>

@push('scripts')

<script src="{{ asset('js/forms/form-builder.min.js')}}"></script>
<script src="{{ asset('js/forms/form-render.min.js')}}"></script>
<script type="text/javascript">

    (function(){
        var container = document.getElementById('fb-rendered-form');
        var formData = {!! $form->json !!}
        var formRenderOpts = {
            container,
            formData,
            dataType: 'json'
        };
        $(container).formRender(formRenderOpts);

        function calc(){
            var total = 0;
            $('input[name="payment[]"]').each(function(i){
                var value = $(this).val();
                if( !$.isNumeric(value) || value == 0 ){
                    value = 0;
                    $(this).val('');
                }
                total += parseFloat(value);
            });

            $('select[name="payment[]"]').each(function(i){
                var value = $(this).val();
                if( !$.isNumeric(value) || value == 0 ){
                    value = 0;
                }

                total += parseFloat(value);
            });

            $('select[name="payment[][]"]').each(function(i){
                var value = $(this).val();

                if($.isArray(value)){
                    value.forEach(function(item, index){
                        total += parseFloat(item);
                    });
                }
                else{
                    total += parseFloat(value);
                }
            });

            $('input[name="total"]').val(total.toFixed(2));
        }

        calc();

        $('select[name="payment[][]"]').on('click', function(e){
            var canSelectMultiple = $(this).prop('multiple');
            if(canSelectMultiple){
                calc();
            }
        });

        $('select[name="payment[]"]').on('change', function(e){
            calc();
        });
        $('input[name="payment[]"]').on('keyup', function(e){
            calc();
        }).keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        $('.calendar').datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '1910:{{ \Carbon\Carbon::now()->addYears(4)->year }}',
            dateFormat: 'yy-mm-dd'
        }).prop('readonly', true);

        $('.readonly').prop('disabled', true);

        @if(request()->has('amount') && array_get($form, 'allow_amount_in_url'))
            $('input.payment:first').val('{{ array_get(request(), "amount", 0) }}');
            calc();
        @endif

        $('form').on('submit', function(e){
            $('button[type="submit"]').prop('disabled', true);
            $('input[name="payment[]"], select[name="payment[]"]').prop('disabled', false);
            
            @if ($hasProfileImage)
                let profileImageDataTransfer = new DataTransfer();
                profileImageDataTransfer.items.add(fileImage);
                document.getElementById('profile_image').files = profileImageDataTransfer.files;
            @endif
            
            setTimeout(function(){$('button[type="submit"]').prop('disabled', false); }, 5000);
        });

        let custom_style = {!! array_get($form,'custom_style','{}') !!};
        if (custom_style.btnText) $('.card').find('.btn.reg').text(custom_style.btnText);
        
        @if ($requiresProfileImage)
        $('button[type="submit"]').click(function () {
            if (!fileImage) {
                Swal.fire('Please upload a profile picture', '', 'warning');
            }
        });
        @endif
    })();
</script>
<script>
    $(document).ready(function () {
        $('input[name="first_name"]').val('{{array_get($contact,'first_name')}}')
        $('input[name="last_name"]').val('{{array_get($contact,'last_name')}}')
        $('input[name="email_1"]').val('{{array_get($contact,'email_1')}}')
        $('input[name="home_phone"]').val('{{array_get($contact,'home_phone')}}')
        $('input[name="work_phone"]').val('{{array_get($contact,'work_phone')}}')
        $('input[name="cell_phone"]').val('{{array_get($contact,'cell_phone')}}')
        
        @if ($hasProfileImage)
            $('.field-profile_image .fb-file-label, #profile_image').hide();
            $('.field-profile_image').append($('#profile-image-container .card'));
            $('#profile-image-container').removeClass('d-none');
            $('#profile_image').prop('required', false);
        @endif
    })
</script>
@endpush

@push('styles')
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
@endpush

    @php
    $custom_style = (array) json_decode(array_get($form,'custom_style'));
    @endphp
@push('styles')
    @if($custom_style)
        <style>
            .card{
                box-shadow: none !important;
                @if(array_get($custom_style,'genFontFam') != 'unset') font-family: '{{array_get($custom_style,'genFontFam')}}' !important @endif;
            }
            .card .card-body{
                background-color: {{array_get($custom_style,'formColor')}} !important;
                padding: {{array_get($custom_style,'formPadding') . 'px'}} !important;
                border: 1px solid {{array_get($custom_style,'borderColor')}} !important;
            }
            .card label{
                color: {{array_get($custom_style,'labelColor')}} !important;
                color: {{array_get($custom_style,'labelColor')}} !important;
            }
            .card input,textarea,select{
                border-radius: {{array_get($custom_style,'fieldsRadius') . 'px'}} !important;
            }
            .card-title{
                color: {{array_get($custom_style,'titleColor')}} !important;
            }
            .card button{
                background: {{array_get($custom_style,'btnBackgroundColor')}} !important;
                color: {{array_get($custom_style,'btnColor')}} !important;
                border: 1px solid !important;
                border-color: {{array_get($custom_style,'btnBorderColor')}} !important;
                font-size: {{array_get($custom_style,'btnFontSize'). 'px'}} !important;
                padding: {{array_get($custom_style,'btnPadding') . 'px'}} !important;
                border-radius: {{array_get($custom_style,'btnRadius') . 'px'}} !important;
            }
            .c-app{
                padding-top: 1.5rem !important;
                align-items: unset !important;
                background: transparent !important;
            }

            .c-legacy-theme{
                background-color: {{array_get($custom_style,'transparentBodyColor') ? 'transparent' : array_get($custom_style,'bodyColor') }} !important;
            }

        </style>
    @endif
@endpush


@endsection
