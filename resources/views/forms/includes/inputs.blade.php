<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>Form Basic Information</h5>
            </div>
            <div class="col-sm-12">
                {{-- FROM NAME --}}
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('form_name', __("Form's name")) }}
                    {{ Form::text('form_name', array_get($form, 'name'), ['class' => 'form-control', 'placeholder' => __('Untitled Form'), 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('name', 'Who should we notify when this form is filled out') }}
                    {{ Form::text('manager', $manager, ['class' => 'form-control autocomplete', 'placeholder' => 'Contacts\'s Name', 'autocomplete' => 'off']) }}
                    {{ Form::hidden('contact_id', array_get($form, 'contact.id')) }}
                </div>
                {{ Form::hidden('json', null, ['id' => 'json']) }}
                <div class="d-flex my-2">

                    @include('includes.ui.switch',['name'=>'do_not_show_form_name','checked'=>array_get($form,'do_not_show_form_name')])
                    <span role="button" type="button" class="ml-2" data-toggle="modal"
                          data-target="#do-not-show-form-name">
                        @lang("Do not show form name")
                        <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h5>Cover Image</h5>
            </div>
            <div class="col-12">
                <div class="m-4 text-center">
                    <label for="image">
                        <img id="renderImage" onmouseenter="$(this).css('opacity','.8')" onmouseout="$(this).css('opacity','1')"
                             src="{{ array_get($form, 'cover') ? asset('storage/form_images/'.array_get($form, 'cover')) : asset('img/blank_placeholder.png') }}"
                             class="img-responsive p-1" style="max-height: 35vh; border: 1px dashed black; cursor:pointer;"/>
                    </label>
                    <h4 class="text-center">Drop an Image or Click to Upload.</h4>
                    <button type="button" class="btn btn-sm btn-secondary d-none" id="unsetImage"><i class="fa fa-undo"></i></button>
                    @if(request()->routeIs('forms.edit') && array_get($form, 'cover'))
                        <button type="button" class="btn btn-sm btn-danger" id="removeImage"><i class="fa fa-trash"></i> Remove Cover Image</button>
                    @endif
                </div>

                <div class="form-group">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" data-render-to=".eventImageUpload" type="file" id="image">
                    <input class="d-none" accept="image/png, image/gif, image/jpeg" name="image" data-render-to=".eventImageUpload" type="file" id="image2">
                    <input type="hidden" name="removeCoverImage">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>Form Payment Settings</h5>
        <div class="d-flex">
            @include('includes.ui.switch',['name'=>'accept_payments','checked'=>array_get($form,'accept_payments')])
            <label for="accept_payments" role="button" class="ml-2">
                @lang('This form accepts payments')
            </label>
        </div>
        <div id="payment_settings" style="display: none">
            <div class="d-flex">
                @include('includes.ui.switch',['name'=>'allow_amount_in_url','checked'=>array_get($form,'allow_amount_in_url')])
                <button type="button" class="btn btn-link ml-2" style="padding-top: 0px; padding-left: 0px; color: #263238;" data-toggle="modal" data-target="#prefill-amount-example">
                    @lang('Allow amount to come from URL')
                    <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip" data-placement="right" data-original-title="You can predetermine the amount in your website, then put the amount in the url when directing people to this form. Click to see an example."></i>
                </button>
            </div>

            <div class="form-group">
                {{ Form::label('campaign_id', 'Fundraiser') }}
                {{ Form::select('campaign_id', $campaigns, null, ['class' => 'form-control']) }}
            </div>
            <div class="form-group">
                {{ Form::label('purpose_id', 'Purpose') }}
                @if ($errors->has('purpose_id'))
                    <span class="help-block text-danger">
                <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
            </span>
                @endif
                {{ Form::select('purpose_id', $charts, null, ['class' => 'form-control']) }}
            </div>
            <div class="d-flex">
                <div class="mr-2">
                    @include('includes.ui.switch',['name'=>'tax_deductible','checked'=>array_get($form,'tax_deductible')])
                </div>
                {{ Form::label('tax_deductible', 'Tax Deductible') }}
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="custom_style">
<div class="card formbuilder-container">
    <div class="card-body">
        <h5>Form Fields</h5>
        <div class="row">
            <div class="col-12 my-2">
                <button type="button" class="float-right btn btn-primary" id="designBtn">Style & Preview</button>
            </div>
        </div>
        <div id="build-wrap-no-payments"></div>
        <div id="build-wrap-payments"></div>
    </div>
</div>
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.css" integrity="sha512-Aix44jXZerxlqPbbSLJ03lEsUch9H/CmnNfWxShD6vJBbboR+rPdDXmKN+/QjISWT80D4wMjtM4Kx7+xkLVywQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    .form-field label{
        color: #000 !important;
        margin-bottom: 0px !important;
        line-height: initial !important;
        font-size: initial !important;
        font-weight: initial !important;
        background: none !important;
        padding-left: 0px !important;
    }
    
    option[value=fineuploader], .multiple-wrap {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.5/cropper.min.js" integrity="sha512-E4KfIuQAc9ZX6zW1IUJROqxrBqJXPuEcDKP6XesMdu2OV4LW7pj8+gkkyx2y646xEV7yxocPbaTtk2LQIJewXw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/forms/form-builder.min.js')}}"></script>
    <script src="{{ asset('js/forms/form-render.min.js')}}"></script>
    <script>
    (function () {
        var showPaymentSettings = function(value) {
            if (value) {
                $('#payment_settings').show();
                $('#build-wrap-payments').show();
                $('#build-wrap-no-payments').hide();
                $('.email-default-payments-yes').removeClass('d-none');
            } else {
                $('#campaign_id').val(1)
                $('#purpose_id').val('')
                $('#payment_settings').hide();
                $('#build-wrap-payments').hide();
                $('#build-wrap-no-payments').show();
                $('.email-default-payments-yes').addClass('d-none');
            }
        }
        var setCampaignId = function(id,first_time) {
            if( id <= 1 ){
                if(!first_time) $('select[name="purpose_id"]').val(1).prop('disabled', false);
                return;
            }
            $.get("{{ route('ajax.get.chartfromcampaign') }}", {campaign_id: id}).done(function(data){
                if( data !== false ){
                    $('select[name="purpose_id"]').val(data.id).prop('disabled', true);
                }
            }).fail(function(data){

            });
        }

        var ASK_BEFORE_CLOSE = true;

        @include('forms.includes.form-builder')

        var options = {
            editOnAdd: true,
            @if( !is_null(old('json')) )
            defaultFields: {!! old('json') !!},
            @else
            defaultFields: {!! array_get($form, 'json', '[]') !!},
            @endif
            inputSets: inputSets,
            fieldRemoveWarn: true,
            controlOrder: controlOrderNoPayments,
            disableFields: disableFieldsNoPayments,
            disabledSubtypes: {
                file: ['fineuploader']
            },
            showActionButtons: false,
            stickyControls: {
                enable: true,
                offset: {
                    top: 100,
                    right: 50,
                    left: 'auto'
                }
            },
            onSave: function(e, formData){
                ASK_BEFORE_CLOSE = false;
                json.value = formData;
                $('#form').submit();
            }
        };

        var formBuilder = $('#build-wrap-no-payments').formBuilder(options);


        var payment_options = {
            editOnAdd: true,
            @if( !is_null(old('json')) )
            defaultFields: {!! old('json') !!},
            @else
            defaultFields: {!! array_get($form, 'json', '[]') !!},
            @endif
            inputSets: inputSets,
            fieldRemoveWarn: true,
            controlOrder: controlOrder,
            disableFields: disableFields,
            disabledSubtypes: {
                file: ['fineuploader']
            },
            showActionButtons: false,
            stickyControls: {
                enable: true,
                offset: {
                    top: 100,
                    right: 50,
                    left: 'auto'
                }
            },
            onSave: function(e, formData){
                ASK_BEFORE_CLOSE = false;
                json.value = formData;
                $('#form').submit();
            },
            typeUserAttrs: {
                text: {
                    className: {
                        label: '&nbsp;',
                        options: {
                            'form-control': 'Allow contact to change prefilled amount',
                            'form-control readonly': 'Do not allow contact to change prefilled amount'
                        }
                    }
                },
                select: {
                    className: {
                        label: '&nbsp;',
                        options: {
                            'form-control': 'Allow contact to change prefilled amount',
                            'form-control readonly': 'Do not allow contact to change prefilled amount'
                        }
                    }
                }
            }
        };

        var formBuilderWithPaymentFields = $('#build-wrap-payments').formBuilder(payment_options);

        var trash = document.getElementById('form-builder-trash');
        var showData = document.getElementById('form-builder-show-data');
        var submit = document.getElementById('form-builder-submit');
        @if (env('APP_ENV') != 'production')
        showData.onclick = function(){
            if(!$('.form-builder-overlay').hasClass('visible')){
                //formBuilder.actions.showData();
                if( $('input[name="accept_payments"]').prop('checked') ){
                    formBuilderWithPaymentFields.actions.showData();
                }
                else{
                    formBuilder.actions.showData();
                }
            }
        };
        @endif
        trash.onclick = function(){
            //formBuilder.actions.clearFields();
            if( $('input[name="accept_payments"]').prop('checked') ){
                formBuilderWithPaymentFields.actions.clearFields();
            }
            else{
                formBuilder.actions.clearFields();
            }
        };

        submit.onclick = function(){
            if( $('input[name="accept_payments"]').prop('checked') && $('select[name="purpose_id"]').val() == "0" ){
                Swal.fire("Select Purpose or Fundraiser",'','info');
                $('select[name="purpose_id"]').focus();
                return false;
            }

            if( $('input[name="accept_payments"]').prop('checked') ){
                var data = formBuilderWithPaymentFields.actions.getData();
            }
            else{
                var data = formBuilder.actions.getData();
            }


            $('#json').val( JSON.stringify(data) );
            $('select[name="purpose_id"]').prop('disabled', false);
            ASK_BEFORE_CLOSE = false;
        };

        @if( array_get($form, 'accept_payments') )
        showPaymentSettings(true)
        $('input[name="accept_payments"]').prop('checked',true);
        @if ($form->campaign_id)
        $('campaign_id').val({{$form->campaign_id}})
        setCampaignId({{$form->campaign_id}},true)
        @endif
        @if ($form->purpose_id) $('purpose_id').val({{$form->purpose_id}}) @endif
        @else
        showPaymentSettings(false)
        @endif

        $(window).bind('beforeunload', function() {
            if(ASK_BEFORE_CLOSE){
                return '@lang("Any string value here forces a dialog box to appear before closing the window.")';
            }
        });

        $('select[name="campaign_id"]').on('change', function(e){
            var id = $(this).val();
            setCampaignId(id)
        });

        $('.autocomplete').autocomplete({
            source: function( request, response ) {
                // Fetch data
                $.ajax({
                    url: "{{ route('contacts.autocomplete') }}",
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
                $('input[name=contact_id]').val(ui.item.id);
            }
        }).on('keydown', function(e){
            if(e.which != 13) {
                $('input[name=contact_id]').val('null');
            }
        });;

        $('input[name="accept_payments"]').on('click', function (e) {
            var checked = $(this).prop('checked');
            showPaymentSettings(checked)
        });

        let custom_style = {!! array_get($form,'custom_style','{}') !!};
        if (custom_style.genFontFam) $('#designModal').find('.card').css('font-family',custom_style.genFontFam);
        if (custom_style.frameHeight) $('#designModal').find('.fakeBody').css('height',custom_style.frameHeight + ' px');
        if (custom_style.btnColor) $('#designModal').find('.card').find('.btn').css('color',custom_style.btnColor);
        if (custom_style.btnBackgroundColor) $('#designModal').find('.card').find('.btn').css('background-color',custom_style.btnBackgroundColor);
        if (custom_style.btnBorderColor) $('#designModal').find('.card').find('.btn').css('border-color',custom_style.btnBorderColor);
        if (custom_style.btnFontSize) $('#designModal').find('.card').find('.btn').css('font-size',custom_style.btnFontSize + 'px');
        if (custom_style.btnPadding) $('#designModal').find('.card').find('.btn').css('padding',custom_style.btnPadding + 'px');
        if (custom_style.btnRadius) $('#designModal').find('.card').find('.btn').css('border-radius',parseInt(custom_style.btnRadius));
        if (custom_style.fieldsRadius) $('#designModal').find('.card').find('input,textarea,select').css('border-radius',parseInt(custom_style.fieldsRadius));
        if (custom_style.formColor) $('#designModal').find('.card').find('.card-body').css('background-color',custom_style.formColor);
        if (custom_style.formPadding) $('#designModal').find('.card').find('.card-body').css('padding',custom_style.formPadding + 'px');
        if (custom_style.borderColor) $('#designModal').find('.card').css('border-color',custom_style.borderColor);
        if (custom_style.labelColor) $('#designModal').find('.card').find('label').css('color',custom_style.labelColor);
        if (custom_style.titleColor) $('#designModal').find('.card').find('.card-title').css('color',custom_style.titleColor);
        if (custom_style.btnText) $('#designModal').find('.card').find('.btn').text(custom_style.btnText);
        if (custom_style.bodyColor) $('#designModal').find('#fakeBody').css('background-color',custom_style.transparentBodyColor ? 'transparent' : custom_style.bodyColor);
        if (custom_style.transparentBodyColor) {
            $('[name="transparentBodyColor"]').prop( "checked", custom_style.transparentBodyColor )
            $('[name="bodyColor"]').parent().hide()
        }

        Object.entries(custom_style).forEach(val => {
            $(`[name="${val[0]}"]`).val(val[1])
        })

        $('#designBtn').click(function () {
            if(!$('.form-builder-overlay').hasClass('visible')) {
                document.querySelectorAll('#nav-fields input').forEach(el => {
                    $(el).trigger('input')
                })
                if ($('[name="do_not_show_form_name"]').is(':checked')) $('#form_title').text('');
                else $('#form_title').text($('#form_name').val());
                let form = $('input[name="accept_payments"]').prop('checked') ? formBuilderWithPaymentFields : formBuilder
                $(document.getElementById('fb-rendered-form')).formRender({
                    container: document.getElementById('fb-rendered-form'),
                    formData: form.formData,
                    dataType: 'json'
                });
                $('.trigger_input_on_render, [type="color"]:not(.dont_trigger_input_on_render)').trigger('input')
                $('#designModal').find('.card').find('label').css('color',$('[name="labelColor"]').val())
                $('#designModal').find('.card').find('input,textarea,select').css('border-radius',parseInt($('[name="fieldsRadius"]').val()));
                $('#designModal').modal('show');
            }
        })

        $('#designModal').keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        $('#designModal').on('hide.coreui.modal',function () {
            $('#fb-rendered-form').empty();
        })

        $('#form').on('submit',function () {
            $('[name="email_content"]').val($('[name="content"]').val());
            
            if ($('[name="create_contact"]').val() == 1) {
                if ($('[name="email_type"]').val() == 'custom') {
                    if (!$('[name="email_subject"]').val()) {
                        Swal.fire('Please enter an email subject', '', 'error');
                        return false;
                    }
                    
                    if (!$('[name="email_content"]').val()) {
                        Swal.fire('Your email content is empty', '', 'error');
                        return false;
                    }
                    
                    $('[name="email_editor_type"]').val(getActiveEditor());
                }
            }
            
            let obj = {}
            $('#customStylesForm').serializeArray().filter(comp => comp.value).forEach(data => {
                obj[data.name] = data.value
            })
            obj.transparentBodyColor = $('[name="transparentBodyColor"]').is(':checked')
            $('[name="custom_style"]').val(JSON.stringify(obj));
            let fData = new FormData(document.getElementById('form'))
            
            let allInputNames = [];
            $('#form input, #form select').each(function (i, el) {
                if ($(el).parents('.formbuilder-container').length === 0) {
                    allInputNames.push($(el).attr('name'));
                }
            });
            $('.formbuilder-container input, .formbuilder-container select').each(function (i, el) {
                if (!allInputNames.includes($(el).attr('name'))) {
                    fData.delete($(el).attr('name'));
                }
            });
            
            if (fileImage) fData.set('image',fileImage);
            $('#overlay').show()
            axios({
                url: document.getElementById('form').getAttribute('action'),
                method: "POST",
                data: fData,
                headers: { "Content-Type": "multipart/form-data" },
            }).then(function(response){
                Swal.fire('Success!',response.data.message,'success');
                if (response.data.redirect) window.location.href = response.data.redirect;
                else window.location.reload();
            }).catch(function (err){

                let message = Object.values(err.response.data).join('. ')
                Swal.fire('Oops!', 'An error occurred, please try again later or contact support','info');
            }).finally(function () {
                $('#overlay').hide()
            })
            return false;
            e.preventDefault()
        })

        $('[name="transparentBodyColor"]').on('change',function (e) {
            if ($(this).is(':checked')) {
                $('[name="bodyColor"]').parent().hide();
                $('#fakeBody').css('background-color','transparent')
            }
            else{
                $('[name="bodyColor"]').parent().show();
                $('#fakeBody').css('background-color',$('[name="bodyColor"]').val())
            }
        })
    })();
</script>
    <script>
        let oldCoverImage = {!! json_encode(array_get($form, 'cover')) !!};
        let fileImage = null;
        (function () {
            let imageInput = document.getElementById('image')
            let dropContainer = document.getElementById('renderImage')
            dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
                evt.preventDefault();
                dropContainer.classList.add('drop-active')
            };

            ['dragleave','dragend'].forEach(ev => {
                dropContainer.addEventListener(ev,function (evt) {
                    dropContainer.classList.remove('drop-active')
                })
            })

            dropContainer.ondrop = function(evt) {
                dropContainer.classList.remove('drop-active')
                if (document.getElementById('image').accept.split(', ').indexOf(evt.dataTransfer.files[0].type) == -1){
                    Swal.fire('Invalid Image','Please drop a valid image','info')
                    return false
                }
                imageInput.files = evt.dataTransfer.files;
                $(imageInput).trigger('input')

                evt.preventDefault();
            };

            let $modal = $('#cropperModal');
            let image = document.getElementById('cropperRenderImage');
            let cropper;
            let file;
            $("#image").on("input change", function(e){
                let files = e.target.files;
                let done = function(url) {
                    image.src = url;
                    $modal.modal('show');
                };
                let reader;
                if (files && files.length > 0) {
                    file = files[0];
                    if (isValidFileImage(file) == false) {
                        Swal.fire('Invalid Image', 'Please select a valid image', 'info')
                        return false
                    }
                    if (URL) {
                        done(URL.createObjectURL(file));
                    } else if (FileReader) {
                        reader = new FileReader();
                        reader.onload = function(e) {
                            done(reader.result);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
            $modal.on('shown.coreui.modal', function() {
                cropper = new Cropper(image, {
                    aspectRatio: 16 / 9,
                    viewMode: 0,
                });
                document.getElementById('zoomRange').value = 1;
                document.getElementById('zoomRange').addEventListener('input',function (e) {
                    console.log(e.target.value)
                    cropper.zoomTo(e.target.value)
                })
            }).on('hide.coreui.modal', function(e) {
                if (document.activeElement.id != 'saveCropImage') document.getElementById('image').value = '';
                cropper.destroy();
                cropper = null;
            });
            $("#saveCropImage").on("click", function() {
                canvas = cropper.getCroppedCanvas();
                canvas.toBlob(function(blob) {
                    fileImage = new File([blob], file.name,{type:file.type, lastModified:new Date().getTime()});
                    document.querySelector('[name="removeCoverImage"]').value = ''
                    let reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function() {
                        let base64data = reader.result;
                        document.getElementById('renderImage').setAttribute('src',base64data) ;
                        document.getElementById('image_style_and_preview').setAttribute('src',base64data);
                        document.getElementById('image_style_and_preview').style.display = 'block';
                        $modal.modal('hide');
                        $('#unsetImage').removeClass('d-none')
                    }
                },file.type);
            })
        })()
    </script>
<script>
    (function () {
        let oldImageOrDefault = "{{ array_get($form, 'cover') ? asset('storage/form_images/'.array_get($form, 'cover')) : asset('img/blank_placeholder.png') }}"
        document.getElementById('unsetImage').addEventListener('click',function () {
            fileImage = null;
            document.getElementById('renderImage').setAttribute('src',oldImageOrDefault)
            document.getElementById('image_style_and_preview').setAttribute('src',oldImageOrDefault)
            document.getElementById('image_style_and_preview').style.display = oldCoverImage ? 'block' : 'none'
            document.querySelector('[name="removeCoverImage"]').value = ''
            $('#unsetImage').addClass('d-none')
            if ($('#removeImage')) $('#removeImage').removeClass('d-none');
        })
        @if(request()->routeIs('forms.edit'))
        let defaultBlankImage = "{{asset('img/blank_placeholder.png') }}";
        document.getElementById('removeImage').addEventListener('click',function () {
            fileImage = null;
            document.querySelector('[name="removeCoverImage"]').value = '1'
            document.getElementById('renderImage').setAttribute('src',defaultBlankImage)
            document.getElementById('image_style_and_preview').style.display = 'none'
            $('#unsetImage').removeClass('d-none')
            $('#removeImage').addClass('d-none')
        })
        @endif
    })()
</script>
@endpush
