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

<div class="row mb-3">
    <div class="col-md-6">
        <h3>{{ $method }} Communication</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div class="row">
                <div class="col-sm-12 col-md-3">
                    {{ Form::label('list_id', 'To List:') }}
                </div>
                <div class="col-sm-12 col-md-8">
                    {{-- {{ Form::select('list_id', $lists, null, ['class' => 'form-control', 'readonly' => true ]) }} --}}
                    {{ Form::select('list_id', $lists, $communication->list_id, ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-sm-12 col-md-3 d-flex">
                    {{ Form::hidden('include_transactions', 0, ['id'=>'include_transactionsHidden']) }}
                    <label for="include_transactions" class="c-switch c-switch-label  c-switch-primary c-switch-sm mr-2">
                        {{ Form::checkbox('include_transactions', 1, $communication->include_transactions, ['id'=>'include_transactions','class'=>"c-switch-input" ]) }}
                        <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                    </label>
                    {{ Form::label('include_transactions', 'Include Transactions') }}
                </div>
                <div id="transaction_options_wrapper" class="col-sm-12 col-md-8" style="{{ !true ? '' : 'display: none' }}">
                    {{ Form::hidden('use_date_range', 1) }}
                    Limit communication for contacts with transactions in this range of dates
                    <div id="transaction_range_wrapper" class="form-group">
                        @include('_partials.human_date_range_selection',[
                            'toDateVal' => $communication->transaction_end_date,
                            'toDateName' => 'transaction_end_date',
                            'fromDateVal' => $communication->transaction_start_date,
                            'fromDateName' => 'transaction_start_date',
                            'defaultSelectedRange' => 'Last Month',
                            'prefix' => 'communications'
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<ul id="communicationTypeTabList" class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link @if($communication->last_action === 'email') active @endif" data-toggle="tab" href="#communication-tab" role="tab" data-communicationType="email">
            <i class="fa fa-envelope-open fa-lg"></i> Email Settings
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if($communication->last_action === 'print') active @endif" data-toggle="tab" href="#communication-tab" role="tab" data-communicationType="print">
            <i class="fa fa-print fa-lg"></i> Print Settings
        </a>
    </li>
</ul>

<div id="communicationTypeContent" class="tab-content">
    <div class="tab-pane active" id="communication-tab" role="tabpanel">
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="show-email">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('subject', 'Subject:') }} <span class="text-danger">*</span>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::text('subject', $communication->subject, ['class' => 'form-control', 'autocomplete' => 'off' ]) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('from_name', 'From Name:') }} <span class="text-danger">*</span>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::text('from_name', array_get($communication, 'from_name', array_get(auth()->user(), 'contact.first_name').' '.array_get(auth()->user(), 'contact.last_name')), ['class' => 'form-control required', 'required' => true, 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group d-none">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('from_email', 'From Email:') }}
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::email('from_email', array_get($communication, 'from_email', array_get(auth()->user(), 'contact.email_1')), ['class' => 'form-control required', 'required' => true, 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('reply_to', 'Reply To:') }} <span class="text-danger">*</span>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::email('reply_to', array_get($communication, 'reply_to', array_get(auth()->user(), 'contact.email_1')), ['class' => 'form-control required', 'required' => true, 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('preview_text', 'Preview Text') }} <i class="fa fa-question-circle-o text-info cursor-help" data-toggle="tooltip" title="This will show next to your subject in the inbox"></i>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::text('preview_text', array_get($communication, 'preview_text'), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 d-flex">
                                {{ Form::hidden('include_public_link', 0, ['id' => 'include_public_linkHidden']) }}
                                <label for="include_public_link" class="c-switch c-switch-label  c-switch-primary c-switch-sm mr-2">
                                    {{ Form::checkbox('include_public_link', 1, $communication->include_public_link, ['id' => 'include_public_link', 'class' => 'c-switch-input' ]) }}
                                    <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                                </label>
                                {{ Form::label('include_public_link', 'Include Public Links') }}<i class="fa fa-question-circle-o text-info mt-1 ml-1" data-toggle="tooltip" title="Enabling this will add public share links at the bottom of the email"></i>
                            </div>

                            @if(!empty($communication->public_link))
                            <div class="col-11 d-none" id="public-link-container">
                                <div class="input-group">
                                    {{ Form::text('public_link', $communication->public_link, ['id' => 'public_link', 'readonly', 'class' => 'form-control']) }}
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-info" type="button" onclick="copy('public_link')">
                                            <i class="fa fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="show-print">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                {{ Form::label('label', 'Label:') }} <span class="text-danger">*</span>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                {{ Form::text('label', $communication->label, ['class' => 'form-control', 'autocomplete' => 'off' ]) }}
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::hidden('content', $communication->content) }}
                {{ Form::hidden('print_content', $communication->print_content) }}
                {{ Form::hidden('email_content_json', $communication->email_content_json) }}
                {{ Form::hidden('print_content_json', $communication->print_content_json) }}
                {{ Form::hidden('email_editor_type', $communication->email_editor_type) }}
                {{ Form::hidden('print_editor_type', $communication->print_editor_type) }}
                {{ Form::hidden('last_action', $communication->last_action) }}
                {{ Form::hidden('loaded_content_template', null, ['id'=> 'loaded_content_template']) }}

                <div class="alert alert-info show-email">@lang('Choose your editor')</div>

                @include('communications.includes.editors')
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="text/javascript">
    var formMethod = '{{ $method }}';
    var emailSettings = {};
    var printSettings = {};
    var emptyTemplateJson = content_templates[{{ $content_templates->where('tenant_id', null)->where('editor_type', 'topol')->where('name', 'Blank')->first()->id }}].content_json;

    if (formMethod === 'Edit') {
        var communication = {!! $communication->toJson() !!};
        emailSettings.activeEditor = communication.email_editor_type;
        emailSettings.content_json = communication.email_content_json ? communication.email_content_json : emptyTemplateJson;
        printSettings.activeEditor = communication.print_editor_type;
        printSettings.content_json = communication.print_content_json ? communication.print_content_json : emptyTemplateJson;

        if (communication.last_action === 'email') {
            loadEmailSettings();
        } else if (communication.last_action === 'print') {
            loadPrintSettings();
        }
    }

    $('#backToSelectEditor').click(function () {
        $('#selectTemplateContainer').slideUp();
        $('#selectEditorContainer').removeClass('d-none').slideDown();
    });

    function setTransactionOptionsVisibilty() {
        if ($('#include_transactions').is(':checked')) {
            $('#transaction_options_wrapper').show();
            $('#transaction_start_date').attr('required',true);
            $('#transaction_end_date').attr('required',true);
            $('button.transaction-code').show();
        }
        else {
            $('#transaction_options_wrapper').hide();
            $('#transaction_start_date').removeAttr('required');
            $('#transaction_end_date').removeAttr('required');
            $('button.transaction-code').hide();
        }
    }

    function setPublicLinkVisibility() {
        if ($('#include_public_link').is(':checked')) {
            $('#public-link-container').removeClass('d-none');
        } else {
            $('#public-link-container').addClass('d-none');
        }
    }

    /**
     * check rich text for empty state and other issues
     * @return {[boolean]} true if content is valid, false otherwise
     */
    function checkRichTextContent(action) {

        // wrap string in a P tag (.text() requires HTML content to work), and remove all spaces from the just the text, then check the length
        let isEmpty = function(html) { return ($("<p>"+html+"</p>").text()).replace(/\s+/g,'').length == 0 }

        let emailContent = getEmailContent();
        let printContent = getPrintContent();

        if ($('#include_transactions').is(':checked')){
            if (action == 'email' && isEmpty(emailContent)) {
                Swal.fire('Email Composer', 'Email content cannot be empty','info');
                return false;
            } else if (action == 'print' && isEmpty(printContent)){
                Swal.fire('Print Composer','Print content cannot be empty','info');
                return false;
            }
        }else{
            let TransactionCodes = new RegExp('\\[:(end_date|start_date|total_amount|list_of_donations|last_transaction_date|last_transaction_amount|last_transaction_purpose):\\]')
            if (action == 'email' && TransactionCodes.test(emailContent)){
                Swal.fire({
                    title : 'Email Composer Including Transaction Codes',
                    text : `Your content includes transaction merge codes. Please check 'Include Transactions' or remove the transaction related merge codes from your contenty`,
                    type: 'info'
                })
                return false;
            } else if (action == 'print' && TransactionCodes.test(printContent)){
                Swal.fire({
                    title : 'Email Composer Including Transaction Codes',
                    text : `Your content includes transaction merge codes. Please check 'Include Transactions' or remove the transaction related merge codes from your contenty`,
                    type: 'info'
                })
                return false;
            } else if (action == 'email' && isEmpty(emailContent)){
                Swal.fire('Email Composer', 'Email content cannot be empty','info');
                return false;
            } else if (action == 'print' && isEmpty(printContent)){
                Swal.fire('Print Composer','Print content cannot be empty','info');
                return false;
            }
        }
        return true;
    }

    function sendTestEmail() {
        if(!checkRichTextContent('email')) {
            return false;
        }

        if (!send_test_email_to_users.length){
            Swal.fire('No contact selected', 'Please search and select contacts to send to.', 'info')
            return false
        }

        let email_markupStr = getEmailContent();
        $('#send_test_email_modal').modal('hide');
        var data = {
            from_name: $("input[name='from_name']").val(),
            from_email: $("input[name='from_email']").val(),
            subject: $("input[name='subject']").val(),
            reply_to: $("input[name='reply_to']").val(),
            content: email_markupStr,
            email_content_json: getEmailJson(),
            action: 'preview',
            send_to: send_test_email_to_users.map(u => u.item.id),
            email_editor_type: getActiveEditor(),
            include_transactions: $('#include_transactions').is(':checked'),
            transaction_start_date: $('input[name="transaction_start_date"]').val(),
            transaction_end_date: $('input[name="transaction_end_date"]').val()
        };
        let edit_contact_url = '{{ route('contacts.edit', auth()->user()->contact->id)}}'
        $.post("{{ route('emails.preview', ['id' => array_get($communication, 'id', 0)]) }}", data).done(function (result) {
            if (result === true) {
                Swal.fire({
                    title: 'Test email was sent',
                    html: `Your test email has been sent.`,
                    type: 'success',
                })
            } else {
                Swal.fire('',result,'error');
            }
            $('#overlay').fadeOut();
        }).fail(function (result) {
            Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            $('#overlay').fadeOut();
        });
    }

    function downloadTestPdf() {
        if (!download_test_pdf_for_users.length){
            Swal.fire('No contact selected', 'Please search and select contacts to send to.', 'info')
            return false
        }

        if(!checkRichTextContent('print')) {
            return false;
        }

        let print_markupStr = getPrintContent();
        $('#overlay').fadeIn();
        $('#download_test_pdf_modal').modal('hide');
        var data = {
            print_content: print_markupStr,
            action: 'testPdf',
            contact_ids: download_test_pdf_for_users.map(u => u.item.id),
            print_editor_type: getActiveEditor(),
            include_transactions: $('#include_transactions').is(':checked') ? 1 : 0,
            transaction_start_date: $('input[name="transaction_start_date"]').val(),
            transaction_end_date: $('input[name="transaction_end_date"]').val()
        };
        $.ajax({
            type: 'POST',
            url: "{{ route('communications.testpdf') }}",
            data: data,
            xhrFields: {
                responseType: 'blob'
            },
            success: function (blob) {
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "testpdf.pdf";
                document.body.appendChild(link);
                link.click();

                $('#overlay').fadeOut();
            },
            error: function () {
                Swal.fire("@lang('Oops! Something went wrong.')",'','error');
                $('#overlay').fadeOut();
            }
        });
    }

    /**
     * Submit form, check rich text, and decide whether to display email or print configuration next
     */
    function onSubmit(e) {
        if(!checkRichTextContent(getActiveCommunicationType())) {
            e.preventDefault(e)
            return false;
        }
        
        if (getActiveCommunicationType() == 'email' && ($('input[name="subject"]').val().length == 0)){
                Swal.fire('Subject Field is required','','info')
                return false
        }

        if (getActiveCommunicationType() == 'print' && ($('input[name="label"]').val().length == 0)){
                Swal.fire('Label Field is required','','info')
                return false
        }

        var email_markupStr = getEmailContent();
        $("input[name='content']").val(email_markupStr);
        var print_markupStr = getPrintContent();
        $("input[name='print_content']").val(print_markupStr);
        $("input[name='email_editor_type']").val(getActiveCommunicationType() === 'email' ? getActiveEditor() : emailSettings.activeEditor);
        $("input[name='print_editor_type']").val(getActiveCommunicationType() === 'print' ? getActiveEditor() : printSettings.activeEditor);
        $("input[name='last_action']").val(getActiveCommunicationType());

        // ensure that include transactions can be removed from communication
        // Thanks https://stackoverflow.com/questions/1809494/post-unchecked-html-checkboxes
        if ($("#include_transactions:checked").length) $('#include_transactionsHidden').attr('disabled',true)
    }

    function getActiveEditor() {
        return $('#editorTabList li a.active').attr('data-tabName');
    }

    function getActiveCommunicationType() {
        return $('#communicationTypeTabList li a.active').attr('data-communicationType');
    }

    // When page loads
    (function () {
        setTransactionOptionsVisibilty();
        $('#include_transactions').change(setTransactionOptionsVisibilty);
        $('.load_template').click(showTemplateModal);
        $('.save_template').click(saveContentTemplate);
        $('.update_template').click(updateContentTemplate);
        $('.update_existing_template').click(updateExistingTemplate);
        $('.copy_content').click(copyEmailContentToPrintContent);
        $('.copy_content_topol').click(copyEmailContentToPrintContentTopol);
        $(document).on('submit', 'form', onSubmit);
        $('button.btn_sendtestemail').click(function () {
            $('#send_test_email_modal').modal('show')
        });
        $('button.btn_test_pdf').click(function () {
            $('#download_test_pdf_modal').modal('show')
        });
        $('.btn_download_test_pdf').click(downloadTestPdf);
        $('#content_template').change(onChangeContentTemplate);

        if (formMethod === 'Edit') {
            setPublicLinkVisibility();
            $('#include_public_link').change(setPublicLinkVisibility);
        }
    })();

    $('#communicationTypeTabList li a').click(function () {
        let communicationType = $(this).attr('data-communicationType');

        if (communicationType === 'email') {
            savePrintSettings();
            loadEmailSettings();

        } else if (communicationType === 'print') {
            saveEmailSettings();
            loadPrintSettings();
        }
    });

    function saveEmailSettings() {
        emailSettings.activeEditor = getActiveEditor();
        topolAction = 'saveEmailSettings';
        TopolPlugin.save();
    }

    function savePrintSettings() {
        printSettings.activeEditor = getActiveEditor();
        topolAction = 'savePrintSettings';
        TopolPlugin.save();
    }

    function loadEmailSettings() {
        $('.show-print').hide();
        $('.show-email').show();
        $('#communicationTypeContent input.required').prop('required', true);
        $('#editorTabList li a').removeClass('active');
        $('#editorTabList li a[data-tabname="'+emailSettings.activeEditor+'"]').addClass('active');
        $('#editorTabContent .tab-pane').removeClass('active');
        $('#editor-'+emailSettings.activeEditor).addClass('active');
    }

    function loadPrintSettings() {
        $('.show-email').hide();
        $('.show-print').show();
        $('#communicationTypeContent input[required]').prop('required', false);
        $('#editorTabList li a').removeClass('active');
        $('#editorTabList li a[data-tabname="'+printSettings.activeEditor+'"]').addClass('active');
        $('#editorTabContent .tab-pane').removeClass('active');
        $('#editor-'+printSettings.activeEditor).addClass('active');
    }

    if (window.location.hash === '#show-templates') {
        $('#selectDragAndDropEditor').trigger('click');
    } else if (window.location.hash === '#show-templates-simple') {
        $('#selectSimpleEditor').trigger('click');
    }

    $('#backToSelectTemplate').click(function () {
        let activeEditor = getActiveEditor() === 'tiny' ? '-simple' : '';
        window.location.replace(window.location.origin+window.location.pathname+'#show-templates'+activeEditor);
        window.location.reload();
    });
</script>
@endpush