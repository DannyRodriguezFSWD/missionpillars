<div class="form-group mt-4">
    <div class="row">
        <div class="col-12">
            {{ Form::mpButton('<i class="fa fa-folder-open"></i> Change Template', ['class' => 'load_template btn-info']) }}
            <button class="btn btn-secondary dropdown-toggle" type="button" id="saveTemplateDropdown" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-save"></i> Save Template
            </button>
            <div class="dropdown-menu" aria-labelledby="saveTemplateDropdown">
                <a class="dropdown-item save_template" type="button">Save as new</a>
                <a class="dropdown-item update_existing_template" type="button">Update existing</a>
            </div>
        </div>
    </div>
</div>

<div class="show-email">
    <div class="form-group">
        <div class="alert alert-info">
            @lang('Tip: Use [Shift]+[Enter] for single line spaces. (Normal [Enter] is 2 lines)')
        </div>

        <textarea id="tinyTextarea-email" class="tinyTextarea" name="tinyTextarea-email" placeholder="Start writing"></textarea>
    </div>
</div>

@push('scripts')
<script>
    initTinyEditor({
        selector: '.tinyTextarea',
        height: 400,
        toolbar: 'undo redo | formatselect | bold italic backcolor | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | mailmerge',
        plugins: 'preview powerpaste casechange importcss tinydrive searchreplace autolink save directionality advcode visualblocks visualchars fullscreen image link template codesample table charmap pagebreak nonbreaking anchor tableofcontents insertdatetime advlist lists checklist wordcount editimage help formatpainter permanentpen pageembed charmap emoticons advtable',
        setup: function (editor) {
            editor.ui.registry.addMenuButton('mailmerge', {
                text: 'Mail Merge',
                fetch: function (callback) {
                    let items = [];

                    mergeTags.forEach(function (item, index) {
                        items.push({
                            type: 'menuitem',
                            text: item.name,
                            onAction: function () {
                                editor.insertContent(item.code);
                            }
                        });
                    });

                    callback(items);
                }
            });

            editor.on('init', function(e) {
                if (formMethod === 'Edit') {
                    if (communication.email_editor_type === 'tiny' && communication.email_content !== null) {
                        tinymce.get("tinyTextarea-email").setContent(communication.email_content);
                    }
                }
                
                editor.on('ObjectResized', function(e) {
                    var target = e.target;
                    if (editor.dom.is(target, 'table')) {
                        tinymce.each(target.rows, function(row) {
                            row.removeAttribute('data-mce-style');
                        });
                    }
                });
            });
        }
    });
</script>
@endpush
