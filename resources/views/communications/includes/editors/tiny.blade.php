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

    <div class="form-group">
        <button name="action" value="save" type="submit" onclick="this.focus()" class="btn btn-warning mr-2 btn_save">
            <i class="fa fa-save"></i> @lang('Save Communication')
        </button>

        <button type="button" class="btn btn-info mr-2 btn_sendtestemail" name="preview">
            <i class="icons icon-envelope-letter"></i> @lang('Send Test Email')
        </button>

        <button name="action" value="email" type="submit" onclick="this.focus()" class="btn btn-success btn_email">
            <i class="fa fa-envelope-open"></i> @lang('Save and Continue')
        </button>
    </div>
</div>

<div class="show-print" style="display: none;">
    <div class="form-group">
        {{ Form::mpButton('<i class="fa fa-copy"></i> Copy From Email Settings', ['class'=>'copy_content btn-info']) }}
    </div>

    <div class="form-group">
        <div class="alert alert-info">
            @lang('Tip: Use [Shift]+[Enter] for single line spaces. (Normal [Enter] is 2 lines)')
        </div>

        <textarea id="tinyTextarea-print" class="tinyTextarea" name="tinyTextarea-print" placeholder="Start writing"></textarea>
    </div>

    <div class="form-group">
        <button name="action" value="save" type="submit" onclick="this.focus()" class="btn btn-warning mr-2">
            <i class="fa fa-save"></i> @lang('Save Communication')
        </button>

        <button type="button" class="btn btn-info mr-2 btn_test_pdf">
            <i class="fa fa-file-pdf-o"></i> @lang('Download Test PDF')
        </button>

        <button name="action" value="print" type="submit" onclick="this.focus()" class="btn btn-success btn_print">
            <i class="fa fa-print"></i> @lang('Configure and Print')
        </button>
    </div>
</div>

@push('scripts')
<script>
    initTinyEditor({
        selector: '.tinyTextarea',
        height: 400,
        toolbar: 'undo redo | formatselect | bold italic backcolor | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | mailmerge',
        plugins: 'preview powerpaste casechange importcss tinydrive searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link template codesample table charmap pagebreak nonbreaking anchor tableofcontents insertdatetime advlist lists checklist wordcount editimage help formatpainter permanentpen pageembed charmap emoticons advtable',
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
                    if (communication.email_editor_type === 'tiny' && communication.content !== null) {
                        tinymce.get("tinyTextarea-email").setContent(communication.content);
                    }

                    if (communication.print_editor_type === 'tiny' && communication.print_content !== null) {
                        tinymce.get("tinyTextarea-print").setContent(communication.print_content);
                    }
                }
                
                @if (session('create_contribution_statement')) document.querySelector('.btn-editor-tempalte.select-template[data-template-id="1"]').click() @endif
                
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
