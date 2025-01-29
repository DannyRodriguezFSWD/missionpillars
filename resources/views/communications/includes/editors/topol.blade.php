<div class="form-group mt-4">
    <div class="row">
        <div class="col-12">
            {{ Form::mpButton('<i class="fa fa-folder-open"></i> Change Template', ['class' => 'load_template btn-info']) }}
            <button class="btn btn-secondary dropdown-toggle" type="button" id="saveTemplateDropdownTopol" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-save"></i> Save Template
            </button>
            <div class="dropdown-menu" aria-labelledby="saveTemplateDropdownTopol">
                <a class="dropdown-item save_template_topol" type="button">Save as new</a>
                <a class="dropdown-item update_existing_template_topol" type="button">Update existing</a>
            </div>
        </div>
    </div>
</div>

<div class="form-group show-print" style="display: none;">
    {{ Form::mpButton('<i class="fa fa-copy"></i> Copy From Email Settings', ['class'=>'copy_content_topol btn-info']) }}
</div>

<div class="form-group">
    <div id="topolContainer" style="width: 100%; height: 800px;"></div>
</div>

<div class="show-email">
    <div class="form-group">
        <button type="button" class="btn btn-warning mr-2 btn_save_topol">
            <i class="fa fa-save"></i> @lang('Save Communication')
        </button>

        <button type="button" class="btn btn-info mr-2 btn_sendtestemail_topol" name="preview">
            <i class="icons icon-envelope-letter"></i> @lang('Send Test Email')
        </button>

        <button type="button" class="btn btn-success btn_email_topol">
            <i class="fa fa-envelope-open"></i> @lang('Save and Continue')
        </button>
    </div>
</div>

<div class="show-print" style="display: none;">
    <div class="form-group">
        <button type="button" class="btn btn-warning mr-2 btn_save_topol">
            <i class="fa fa-save"></i> @lang('Save Communication')
        </button>
        
        <button name="action" value="print" type="submit" class="btn btn-success btn_print_topol">
            <i class="fa fa-print"></i> @lang('Configure and Print')
        </button>
    </div>
</div>

@push('scripts')
<script>
let mergeTagsItems = [];
let topolAction = null;

mergeTags.forEach(function(item, index) {
    mergeTagsItems.push({
        value: item.code,
        text: item.name,
        label: item.title
    });
});

const TOPOL_OPTIONS = {
    id: "#topolContainer",
    authorize: {
        apiKey: "{{ env('TOPOL_API_KEY') }}",
        userId: "continuetogive_{{ config('app.env') }}_{{ auth()->user()->tenant->id }}"
    },
    topBarOptions: [
        "undoRedo",
        "changePreview",
        "previewSize"
    ],
    windowBar: [
        'fullscreen'
    ],
    customFonts: {
        override: false,
        fonts: [{
            label: 'Nimbus Sans L',
            style: '"Nimbus Sans L", sans-serif',
            url: '{{ config('app.url') }}/fonts/NimbusSansL/NimbusSanL.css'
        }]
    },
    callbacks: {
        onSave: function(json, html) {
            if (getActiveCommunicationType() === 'email') {
                $("input[name='content']").val(html);
                $("input[name='email_content_json']").val(JSON.stringify(json));
            } else if (getActiveCommunicationType() === 'print') {
                $("input[name='print_content']").val(html);
                $("input[name='print_content_json']").val(JSON.stringify(json));
            }

            switch (topolAction) {
                case 'saveTemplate':
                    saveContentTemplate();
                    break;
                case 'updateTemplate':
                    updateContentTemplate();
                    break;
                case 'updateExistingTemplate':
                    updateExistingTemplate();
                    break;
                case 'sendTestEmail':
                    sendTestEmail();
                    break;
                case 'submit':
                    $('.btn_email').trigger('click');
                    break;
                case 'submitPrint':
                    $('.btn_print').trigger('click');
                    break;
                case 'saveEmailSettings':
                    $("input[name='content']").val(html);
                    $("input[name='email_content_json']").val(JSON.stringify(json));
                    emailSettings.content_json = JSON.stringify(json);
                    // uncomment this if you manage to make topol work for print
                    //TopolPlugin.load(printSettings.content_json);
                    break;
                case 'savePrintSettings':
                    $("input[name='print_content']").val(html);
                    $("input[name='print_content_json']").val(JSON.stringify(json));
                    printSettings.content_json = JSON.stringify(json);
                    // uncomment this if you manage to make topol work for print
                    //TopolPlugin.load(emailSettings.content_json);
                    break;
                case 'initEmailAndPrintSettings':
                    if (formMethod === 'Create') {
                        emailSettings.content_json = JSON.stringify(json);
                        printSettings.content_json = JSON.stringify(json);
                    }
                    break;
                case 'save':
                    $('.btn_save').trigger('click');
                    break;
            }
        },
        onInit() {
            topolAction = 'initEmailAndPrintSettings';
            TopolPlugin.save();
        }
    },
    mergeTags: mergeTagsItems,
    disableAlerts: true
};

TopolPlugin.init(TOPOL_OPTIONS);

if (formMethod === 'Create' || (formMethod === 'Edit' && communication.email_editor_type === 'tiny')) {
    TopolPlugin.load(emptyTemplateJson);
} else if (formMethod === 'Edit') {
    TopolPlugin.load(communication.email_content_json);
}

$('.save_template_topol').click(function () {
    topolAction = 'saveTemplate';
    TopolPlugin.save();
});

$('.update_template_topol').click(function () {
    topolAction = 'updateTemplate';
    TopolPlugin.save();
});

$('.update_existing_template_topol').click(function () {
    topolAction = 'updateExistingTemplate';
    TopolPlugin.save();
});

$('.btn_sendtestemail_topol').click(function () {
    $('#send_test_email_modal').modal('show')
});

$('.btn_send_test_message').click(function () {
    $('#overlay').fadeIn();
    topolAction = 'sendTestEmail';
    TopolPlugin.save();
});

$('.btn_email_topol').click(function () {
    topolAction = 'submit';
    TopolPlugin.save();
});

$('.btn_print_topol').click(function () {
    topolAction = 'submitPrint';
    TopolPlugin.save();
});

$('.btn_save_topol').click(function () {
    topolAction = 'save';
    TopolPlugin.save();
});

</script>
@endpush
