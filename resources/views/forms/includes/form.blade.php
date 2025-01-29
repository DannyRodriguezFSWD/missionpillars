@php
Form::macro('mpButton', function($label, $attributes = []) {
    $class = 'btn btn-secondary';
    if (array_key_exists('class', $attributes)) {
        $class .= " {$attributes['class']}";
    }
    $attributes['class'] = $class;
    return Form::button($label, $attributes);
});
@endphp

<div class="text-right pb-2 position-sticky" style="top: 60px; pointer-events: none; z-index: 2">
    <div class="d-inline" style="pointer-events: auto">
        <button class="btn btn-danger" type="button" id="form-builder-trash">
            <span class="fa fa-trash"></span>
            @lang('Trash all fields')
        </button>
        @if (env('APP_ENV') != 'production')
            <button class="btn btn-secondary" type="button" id="form-builder-show-data">
                <span class="fa fa-code"></span>
                @lang('Show Code')
            </button>
        @endif
        <button class="btn btn-primary" type="submit" id="form-builder-submit">
            <span class="fa fa-edit"></span>
            @lang('Save')
        </button>
        @if (!is_null($form))
            <a href="{{ route('forms.share', ['id' => $form->uuid]) }}" class="btn btn-info" target="_blank">View Form</a>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#share-modal-{{ array_get($form, 'id') }}">
                <span class="fa fa-share"></span>
                @lang('Share')
            </button>
        @endif
    </div>
</div>
@if(!is_null($form))
    @include('forms.includes.share-modal')
@endif

@includeIf('forms.includes.inputs')
<div class="card">
    <div class="card-body">
        <h5>Options</h5>

        <label for="">Respondents</label>
        <div class="d-flex">
            <div class="mr-2">
                @include('includes.ui.switch',['name'=>'create_contact','checked'=>array_get($form,'create_contact')])
            </div>
            @lang('Create or update contact based off matching email when form has an email field')
        </div>

        <div id="contact-auto-tags" class="d-flex">
            <div class="mr-2">
                @include('includes.ui.switch',['name'=>'auto_tag_form','checked'=>array_get($form,'auto_tag_form')])
            </div>
            @lang('Auto Tag Respondents')
        </div>
        <div id="form-tags" style="display: none;">
            @includeIf('forms.includes.tags')
            <hr/>
        </div>
        <div id="contact-custom-header" class="mt-4">
            <h5>@lang('Form Confirmation Email')</h5>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="btn-group" id="formEmailType">
                        <button type="button" class="btn btn-secondary" data-email-type="default">Default</button>
                        <button type="button" class="btn btn-secondary" data-email-type="custom">Custom</button>
                        <button type="button" class="btn btn-secondary" data-email-type="no_email">No Email</button>
                    </div>
                </div>
            </div>

            <div class="row mb-2 d-none" data-show-if="default">
                <div class="col-12">
                    <p><i class="fa fa-info-circle text-info"></i> A default confirmation email will be sent to the contact that fills this form.</p>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#defaultEmailModal"><i class="fa fa-search"></i> View Email</button>
                </div>
            </div>

            <div class="row mb-2 d-none" data-show-if="custom">
                <div class="col-12">
                    <p><i class="fa fa-info-circle text-info"></i> Customize the confirmation email that will be sent to the contact that fills this form.</p>

                    <div class="row">
                        <div class="col-12">
                            <h6>@lang('Email Subject')</h6>
                            <div class="form-group">
                                {{ Form::text('email_subject', array_get($form, 'email_subject'), ['class' => 'form-control', 'placeholder' => __('Email Subject'), 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#formEmailModal">
                                <i class="fa fa-edit"></i> @lang('Edit Your Email')
                            </button>
                        </div>
                    </div>

                    {{ Form::hidden('email_type') }}
                    {{ Form::hidden('content') }}
                    {{ Form::hidden('email_content') }}
                    {{ Form::hidden('email_content_json') }}
                    {{ Form::hidden('email_editor_type') }}
                    {{ Form::hidden('loaded_content_template', null, ['id'=> 'loaded_content_template']) }}
                </div>
            </div>

            <div class="row mb-2 d-none" data-show-if="no_email">
                <div class="col-12">
                    <p><i class="fa fa-info-circle text-info"></i> No confirmation email will be sent to the contact that fills this form.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5>
            @lang('Custom Landing Page')
        </h5>
        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#custom-landing-page" style="padding-top: 0px; padding-left: 0px; color: #263238;">
            @lang('Custom URL')
            <i class="fa fa-question-circle-o text-primary" style="cursor: pointer;" data-toggle="tooltip" data-placement="right" data-original-title="You can enter a custom URL if you want users redirected somewhere specific after completion. Full URL is required (examples: https://www.example.com, https://example.com)"></i>
        </button>
        <div class="form-group">
            {{ Form::text('custom_landing_page', array_get($form, 'custom_landing_page'), ['class' => 'form-control', 'placeholder' => 'Custom URL', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>



<div class="modal" id="designModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Style & Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row p2">
                    <div id="fakeBody" style="border: 1px dashed black; overflow: auto" class="col-md-6 pt-4">
                        <div class="col-12">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <form action="#" onsubmit="(e) => {e.preventDefault()}">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h2 id="form_title" class="card-title"></h2>
                                                <img src="{{ asset('storage/form_images/'.array_get($form, 'cover')) }}" class="img-responsive" id="image_style_and_preview" style="max-height: 33vh; margin: 0 auto; @if(empty(array_get($form, 'cover'))) display: none @endif"/>
                                                <div id="fb-rendered-form"></div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="button" class="btn">
                                                Submit form
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form id="customStylesForm" action="#" onsubmit="(e) => {e.preventDefault()}">
                            <div class="nav nav-tabs mb-2" id="nav-tab" role="tablist">
                                <a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">General</a>
                                <a class="nav-item nav-link" id="nav-button-tab" data-toggle="tab" href="#nav-button" role="tab" aria-controls="nav-button" aria-selected="false">Button</a>
                                <a class="nav-item nav-link" id="nav-fields-tab" data-toggle="tab" href="#nav-fields" role="tab" aria-controls="nav-fields" aria-selected="false">Form Fields</a>
                            </div>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">
                                    <div class="form-group">
                                        <p for="">Font Family</p>
                                        <select name="genFontFam" id="" oninput="$('#designModal').find('.card-body').css('font-family',this.value)" class="form-control">
                                            <option value="unset">Default</option>
                                            <option value="Arial">Arial</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Impact">Impact</option>
                                            <option value="Tahoma">Tahoma</option>
                                            <option value="Times New Roman">Times New Roman</option>
                                            <option value="Verdana">Verdana</option>
                                        </select>
                                    </div>
                                    <div class="form-group d-flex">
                                        <label class="mr-2">Transparent background</label>
                                        @include('includes.ui.switch',['name'=>'transparentBodyColor','checked'=> false])
                                    </div>
                                    <div class="form-group">
                                        <p for="">Frame Height</p>
                                        <input oninput="$('#fakeBody').css('height',event.target.value + 'px')" name="frameHeight" class="form-control trigger_input_on_render" type="number" value="700">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Background</p>
                                        <input oninput="$('#fakeBody').css('background-color',event.target.value)" name="bodyColor" class="form-control dont_trigger_input_on_render" type="color" value="#ffffff">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Form Background</p>
                                        <input oninput="$('#fakeBody').find('.card-body').css('background-color',event.target.value)" name="formColor" class="form-control" type="color" value="#ffffff">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Title Color</p>
                                        <input oninput="$('#fakeBody').find('#form_title').css('color', this.value)" name="titleColor" type="color" value="#000000" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Border Color</p>
                                        <input oninput="$('#fakeBody').find('.card').css('border-color', event.target.value)" name="borderColor" type="color" value="#000000" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Padding</label>
                                        <input name="formPadding" oninput="$('#fakeBody').find('.card-body').css('padding',this.value + 'px')" type="number" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade show" id="nav-button" role="tabpanel" aria-labelledby="nav-button-tab">
                                    <div class="form-group">
                                        <p for="">Background Color</p>
                                        <input name="btnBackgroundColor" oninput="$('#fakeBody').find('button').css('background-color',this.value)" type="color" value="#ffffff" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Text</p>
                                        <input name="btnText" oninput="$('#fakeBody').find('button').text(this.value)" type="text" value="Submit Form" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Text Color</p>
                                        <input name="btnColor" oninput="$('#fakeBody').find('button').css('color',this.value)" type="color" value="#000000" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Border Color</p>
                                        <input name="btnBorderColor" oninput="$('#fakeBody').find('button').css('border-color',this.value)" type="color" value="#000000" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Padding</label>
                                        <input name="btnPadding" oninput="$('#fakeBody').find('button').css('padding',this.value + 'px')" type="number" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Text Size</label>
                                        <input name="btnFontSize" oninput="$('#fakeBody').find('button').css('font-size',this.value + 'px')" type="number" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Border Radius</label>
                                        <input name="btnRadius" oninput="$('#fakeBody').find('button').css('border-radius',parseInt(this.value))" type="number" value="" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade show" id="nav-fields" role="tabpanel" aria-labelledby="nav-fields-tab">
                                    <div class="form-group">
                                        <label for="">Border Radius</label>
                                        <input name="fieldsRadius" oninput="$('#fakeBody').find('.card').find('input,select,textarea').css('border-radius',parseInt(this.value))" type="number" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <p for="">Label Color</p>
                                        <input name="labelColor" oninput="$('#fakeBody').find('.card').find('label').css('color',this.value)" type="color" value="#000000" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cropperModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crop Image to 16 / 9 Ratio</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <img src="" class="eventImageUpload img-fluid" id="cropperRenderImage" alt="">
                    </div>
                    <div class="col-12 text-center mt-2">
                        <input type="range" step="0.1" min="0" max="4" id="zoomRange" class="form-control-range">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="saveCropImage" type="button" class="btn btn-success">Ok</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="defaultEmailModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Subject: Form submission</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                @include('forms.includes.email-default')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>    

@includeIf('forms.includes.prefill-amount-example')
@push('scripts')
    <script>
        // WHEN READY
        (function () {
            // Setup Contact settings visiblity
            var showContactSettings = function(value) {
                if (value) {
                    $('#contact-auto-tags').show(200);
                    $('#form-tags').show(200);
                    $('#contact-custom-header').show(200);

                    showAutoTagSettings($('input[name="auto_tag_form"]').prop('checked'))
                } else {
                    $('#contact-auto-tags').hide(200);
                    $('#form-tags').hide(200);
                    $('#contact-custom-header').hide(200);
                    $('input[name="auto_tag_form"]').prop('checked',false);
                }
            }

            var showAutoTagSettings = function(checked) {
                if( checked ){
                    $('#form-tags').fadeIn();
                }
                else{
                    $('#form-tags').fadeOut();
                    $('ul li.event-tag input[type="checkbox"]').prop('checked', false);
                }
            }

            @if( array_get($form, 'auto_tag_form') != 1)
            showAutoTagSettings(false)
            @endif

            @if(array_get($form, 'create_contact'))
                showContactSettings(true)

                @if( array_get($form, 'auto_tag_form') == 1)
                showAutoTagSettings(true)
                @endif

            @else
                showAutoTagSettings(false)
                showContactSettings(false)
            @endif;

            $('input[name="auto_tag_form"]').on('click', function(e){
                var checked = $(this).prop('checked');
                showAutoTagSettings(checked)
            });

            $('input[name="create_contact"]').on('click', function (e) {
                var checked = $(this).prop('checked');
                showContactSettings(checked)
            });
        })();
        
        var formMethod = '{{ $method }}';
        var emailSettings = {activeEditor: 'topol'};
        var printSettings = {activeEditor: 'topol'};
        var emptyTemplateJson = content_templates[{{ $content_templates->where('tenant_id', null)->where('editor_type', 'topol')->where('name', 'Blank')->first()->id }}].content_json;
        $('.load_template').click(showTemplateModal);
        $('.save_template').click(saveContentTemplate);
        $('.update_template').click(updateContentTemplate);
        $('.update_existing_template').click(updateExistingTemplate);
        
        @if ($form)
        if (formMethod === 'Edit') {
            var communication = {!! $form->toJson() !!};
            
            @if (array_get($form, 'email_type') === 'custom' && array_get($form, 'email_editor_type') === 'tiny')
            $('#editorTabList .nav-link[data-tabname="topol"], #editor-topol').removeClass('active')
            $('#editorTabList .nav-link[data-tabname="tiny"], #editor-tiny').addClass('active')
            @endif
        }
        @endif
        
        function getActiveEditor() {
            return $('#editorTabList li a.active').attr('data-tabName');
        }
        
        function getActiveCommunicationType() {
            return 'email';
        }
        
        function saveFormEmail() {
            var editor = getActiveEditor()
            
            if (editor === 'topol') {
                TopolPlugin.save();
            } else {
                $("input[name='content']").val(getEmailContent());
            }
            
            $('#formEmailModal').modal('hide');
        }
        
        $('#formEmailType button').click(function () {
            $('#formEmailType button.btn-primary').removeClass('btn-primary').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-primary');
            
            var emailType = $(this).data('email-type');
            $('[data-show-if]').addClass('d-none');
            $('[data-show-if="'+emailType+'"]').removeClass('d-none');
            $('[name="email_type"]').val(emailType);
        });
        
        $('[data-email-type="{{ array_get($form, 'email_type', 'default') }}"]').removeClass('btn-secondary').addClass('btn-primary');
        $('[data-show-if="{{ array_get($form, 'email_type', 'default') }}"]').removeClass('d-none');
    </script>
@endpush

@include('communications.includes.templatepreviewmodal')
@include('communications.includes.templatelistmodal')
@include('communications.includes.scripts.templatescripts')
@include('forms.includes.email-edit-modal')
