

<div class="show-email">
    <div class="form-group">
        <div class="alert alert-info">
            @lang('Tip: Use [Shift]+[Enter] for single line spaces. (Normal [Enter] is 2 lines)')
        </div>

        <textarea id="tinyTextarea-email" class="tinyTextarea" name="tinyTextarea-email" placeholder="Start writing">
            @if ($html)
                {!! $html !!}
            @endif
        </textarea>
    </div>

    <div class="form-group">
        <button name="action" value="save" type="submit" onclick="this.focus()" class="btn btn-warning mr-2 btn_save">
            <i class="fa fa-save"></i> Save Templates
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
           

            editor.on('init', function(e) {
               
                
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
