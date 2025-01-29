@push('scripts')
<script type="text/javascript">
var content_templates = {!! $content_templates->keyBy('id')->toJson() !!}
var namingError;

function templateOverlay(selector) {
    $(selector).mouseenter(function () {
        $(this).find('.template-buttons').removeClass('d-none');
    }).mouseleave(function () {
        $(this).find('.template-buttons').addClass('d-none');
    });
}

templateOverlay('.template-container');

function previewTemplate(button) {
    let template = content_templates[$(button).attr('data-template-id')];
    $('#loaded_content_template').val(template.id)

    $('#templatePreviewModal .modal-body').html('<iframe frameborder="0" class="template-preview-iframe w-100" srcdoc="'+template.content_html_encoded+'"></iframe>');

    $('#screenMobile').prop('checked', false);
    $('#selectSecreenMobile').removeClass('active');
    $('#screenDesktop').prop('checked', true);
    $('#selectSecreenDesktop').addClass('active');

    $('#templatePreviewModal').modal('show');
}

$('#screenMobile, #screenDesktop').change(function () {
    $('.template-preview-iframe').toggleClass('w-100').toggleClass('w-50');
});

$('#selectTemplate').click(function () {
    let template = content_templates[$('#loaded_content_template').val()];
    loadTemplate(template);
});

function selectTemplate(button) {
    let template = content_templates[$(button).attr('data-template-id')];
    loadTemplate(template);
}

function loadTemplate(template) {
    $('#loaded_content_template').val(template.id);

    let activeCommunicationType = getActiveCommunicationType();

    switch (template.editor_type) {
        case 'tiny':
            tinymce.get("tinyTextarea-"+activeCommunicationType).setContent(template.content);
            break;
        case 'topol':
            TopolPlugin.load(template.content_json);

            if (activeCommunicationType === 'email') {
                emailSettings.content_json = template.content_json;
                emailSettings.activeEditor = template.editor_type;
            } else {
                printSettings.content_json = template.content_json;
                printSettings.activeEditor = template.editor_type;
            }
            break;
    }

    $('#selectTemplateContainer').slideUp();
    $('#communicationFormContainer').removeClass('d-none').slideDown();
    $('#templateListModal').modal('hide');
}

function deleteTemplate(button) {
    let url = "{{ route('ajax.statementtemplate.destroy','TEMPLATEID') }}";
    url = url.replace('TEMPLATEID', $(button).attr('data-template-id'));

    Swal.fire({
        title: 'Are you sure you want to delete this template?',
        type: 'question',
        showCancelButton: true
    }).then(res => {
        if (res.value){
            $.ajax(url, {
                method: 'DELETE',
                beforeSend: function () {
                    Swal.fire('Deleting template', '', 'info');
                    Swal.showLoading();
                }
            })
            .done(function () {
                $(button).parents('.template-container').parent().remove();
                Swal.fire('Template deleted successfully', '', 'success');
            })
            .fail(response => {
                Swal.fire('Error deleting template','', 'error');
                console.log(response)
            });
        }
    });
}

function showTemplateModal() {
    loadFromTemplate = true
    prev_email_content = getEmailContent();
    prev_print_content = getPrintContent();

    $('#overlay').fadeIn();

    $.get("{{ route('ajax.statementtemplate.listmodal') }}").done(function (result) {
        $('#templateListModal .modal-body').html(result.view);
        $('#templateListModal .template-card').hide();
        $('#templateListModal .template-card-'+getActiveEditor()).show();
        templateOverlay('#templateListModal .template-container');
        $('#templateListModal').modal('show');
        $('#overlay').fadeOut();
        content_templates = JSON.parse(result.content_templates);
    }).fail(function (result) {
        console.log(result.responseText);
        Swal.fire("@lang('Oops! Something went wrong. [404]')",'','error');
        $('#overlay').fadeOut();
    });
}

function getContentTemplate(template) {
    if (!template) {
        template = {};
    }

    template.editor_type = getActiveEditor();
    template.content = getActiveCommunicationType() === 'email' ? getEmailContent() : getPrintContent();
    template.content_json = getActiveCommunicationType() === 'email' ? getEmailJson() : getPrintJson();

    return template;
}

function findTemplate(name) {
    var template = undefined
    for (i in content_templates) { if (content_templates[i].name == name) { template = content_templates[i]; break; } }
    return template
}

function saveContentTemplate() {
    Swal.fire({
        title: 'Insert template name',
        html: '<center><input type="text" class="form-control w-75" id="templateName" placeholder="Template name" /></center>',
        type: 'question',
        footer: namingError,
        showCancelButton: true
    }).then(res => {
        if (res.value) {
            var template = getContentTemplate()

            template.name = $('#templateName').val();

            if (!template.name) {
                namingError = '<i class="fa fa-exclamation-triangle text-danger mt-1 mr-1"></i> Please insert a name for the tempalte.';
                saveContentTemplate()
                namingError = '';
                return;
            }
            // TODO consider making AJAX call getting the following from the result (preferred) - it is technically possible to create duplicates via two different, simultaneous running tabs/sessions
            var existing_template = findTemplate(template.name)
            var nameisglobal = existing_template && ! existing_template.tenant_id

            if (nameisglobal) {
                namingError = '<i class="fa fa-exclamation-triangle text-danger mt-1 mr-1"></i>' + template.name + ' is the name of a stock template.<br>Please use a different name.';
                saveContentTemplate()
                namingError = '';
                return
            }

            if (existing_template) {
                Swal.fire({
                    title: 'Template name exists',
                    text: 'This template name exists. Update?',
                    type: 'question',
                    showCancelButton: true
                }).then(res => {
                    if (res.value){
                        $('#content_template').val(existing_template.id)
                        $('#loaded_content_template').val(existing_template.id)
                        updateContentTemplate(false)
                    }
                })
                return
            }

            $.post({
                url: "{{ route('ajax.statementtemplate.store') }}",
                data: template,
                beforeSend: function () {
                    Swal.fire('Saving template', '', 'info');
                    Swal.showLoading();
                }
            })
            .done(function (result) {
                template = result

                $('#content_template').val(template.id);
                content_templates[template.id] = template;
                $('#loaded_content_template').val(template.id);

                Swal.fire('Template saved successfully', '', 'success');
            })
            .fail(response => {
                Swal.fire('Error saving template','','error');
                console.log(response)
            })
        }
    });
}

let updateExistingTemplate = async function () {
    let options = {}
    Object.values(content_templates).filter(template => template.tenant_id).forEach(template => {
        if (template.editor_type === getActiveEditor()) {
            options[template.id] = template.name
        }
    })
    let template_id = await Swal.fire({
        title: 'Update Existing Template',
        input: 'select',
        inputOptions: options,
        inputPlaceholder: 'Select template',
        type: 'question',
        footer: namingError,
        showCancelButton: true
    })
    if (template_id.value){
        updateContentTemplate(true,template_id.value)
    }
}

function updateContentTemplate(confirm = true, template_id = null) {
    var templateid = template_id || $('#loaded_content_template').val();
    var template = content_templates[templateid]
    var url = "{{ route('ajax.statementtemplate.update','TEMPLATEID') }}"
    url = url.replace('TEMPLATEID',template.id)

    if (confirm) {
        Swal.fire({
            title: 'Are you sure you want to update this template?',
            type: 'question',
            showCancelButton: true
        }).then(res => {
            if (res.value){
                getContentTemplate(template)

                $.ajax(url, {
                    method: 'PUT',
                    data: template,
                    beforeSend: function () {
                        Swal.fire('Updating template', '', 'info')
                        Swal.showLoading()
                    }
                })
                .done(response => {
                    content_templates[templateid] = template
                    Swal.fire('Template updated successfully', '', 'success');
                })
                .fail(response => {
                    Swal.fire('Error updating template '.template.name,'','error');
                    console.log(response)
                })
            }
        })
    } else {
        getContentTemplate(template)

        $.ajax(url, {
            method: 'PUT',
            data: template,
            beforeSend: function () {
                Swal.fire('Updating template', '', 'info')
                Swal.showLoading()
            }
        })
        .done(response => {
            content_templates[templateid] = template
            Swal.fire('Template updated successfully', '', 'success');
        })
        .fail(response => {
            Swal.fire('Error updating template '.template.name,'','error');
            console.log(response)
        })
    }
}

function copyEmailContentToPrintContent() {
    if (printSettings.activeEditor === emailSettings.activeEditor) {
        var content = getEmailContent();

        let activeTab = getActiveEditor();

        switch (activeTab) {
            case 'tiny':
                tinymce.get("tinyTextarea-print").setContent(content);
                break;
        }
    }
}

function copyEmailContentToPrintContentTopol() {
    printSettings.content_json = emailSettings.content_json;
    TopolPlugin.load(printSettings.content_json);
}

/**
 * When a content template is selected in the dropbdown, display 'update template' UI if the template is the current
 * @param  {[event]} e
 */
function onChangeContentTemplate(e) {
    var template = {}
    var selected_template = $('#content_template option:selected').val()
    template.id = $('#loaded_content_template').val()
    if (selected_template == template.id) $('.update_template').show(20)
    else $('.update_template').hide(20)
}

 /**
 * Get email contet dynamically from the chosen editor
 */
function getEmailContent() {
    let activeEditor = getActiveCommunicationType() === 'email' ? getActiveEditor() : emailSettings.activeEditor;

    switch (activeEditor) {
        case 'tiny':
            return tinymce.get("tinyTextarea-email").getContent();
            break;
        case 'topol':
        case 'stripo':
            return $("input[name='content']").val();
            break;
    }
}

/**
 * Get print contet dynamically from the chosen editor
 */
function getPrintContent() {
    let activeEditor = getActiveCommunicationType() === 'print' ? getActiveEditor() : printSettings.activeEditor;

    switch (activeEditor) {
        case 'tiny':
            return tinymce.get("tinyTextarea-print").getContent();
            break;
        case 'topol':
        case 'stripo':
            return $("input[name='print_content']").val();
            break;
    }
}

/**
 * Get email json dynamically from the chosen editor
 */
function getEmailJson() {
    let activeEditor = getActiveCommunicationType() === 'email' ? getActiveEditor() : emailSettings.activeEditor;

    switch (activeEditor) {
        case 'topol':
            return $("input[name='email_content_json']").val();
            break;
        default :
            return null;
            break;
    }
}

/**
 * Get print json dynamically from the chosen editor
 */
function getPrintJson() {
    let activeEditor = getActiveCommunicationType() === 'print' ? getActiveEditor() : printSettings.activeEditor;

    switch (activeEditor) {
        case 'topol':
            return $("input[name='print_content_json']").val();
            break;
        default :
            return null;
            break;
    }
}
</script>
@endpush
