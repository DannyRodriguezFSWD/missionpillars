<div class="row mt-4">
    <div class="col-12">
        <button class="btn btn-info load_template" type="button">
            <i class="fa fa-folder-open"></i> Change Template
        </button>
        <button class="btn btn-secondary dropdown-toggle" type="button" id="saveTemplateDropdownTopol" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-save"></i> Save Template
        </button>
        <div class="dropdown-menu" aria-labelledby="saveTemplateDropdownTopol">
            <a class="dropdown-item save_template_topol" type="button">Save as new</a>
            <a class="dropdown-item update_existing_template_topol" type="button">Update existing</a>
        </div>
    </div>
</div>

<div id="topolContainer" class="mt-2" style="width: 100%; height: calc(100vh - 360px);"></div>

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
    callbacks: {
        onSave: function(json, html) {
            $("input[name='content']").val(html);
            $("input[name='email_content_json']").val(JSON.stringify(json));

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
                case 'saveEmailSettings':
                    $("input[name='content']").val(html);
                    $("input[name='email_content_json']").val(JSON.stringify(json));
                    emailSettings.content_json = JSON.stringify(json);
                    break;
            }
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
</script>
@endpush
