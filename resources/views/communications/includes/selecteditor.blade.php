<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    
    <div class="card-body">
        <h3 class="mb-1">@lang('Choose your editor')</h3>
        <p class="mb-3">@lang('You can choose between a really cool drag and drop editor or keep it simple with a rich text editor.')</p>

        <div class="row">
            <div class="col-sm-6 mb-3 mb-sm-0">
                <button class="btn btn-block btn-outline-primary btn-lg p-5 show-templates" id="selectDragAndDropEditor" data-editorType="topol">
                    <p class="mb-2">@lang('Drag and Drop Editor')</p>
                    <img class="img-responsive" src="{{ url('/img/communications/drag and drop editor.gif') }}" />
                </button>
            </div>

            <div class="col-sm-6">
                <button class="btn btn-block btn-outline-primary btn-lg p-5 h-100 show-templates" id="selectSimpleEditor" data-editorType="tiny">
                    <p class="mb-2">@lang('Simple Editor')</p>
                    <img class="img-responsive" src="{{ url('/img/communications/simple editor.PNG') }}" />
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-footer">&nbsp;</div>
</div>

@push('scripts')
<script>
    $('.show-templates').click(function () {
        let editorType = $(this).attr('data-editorType');
        
        $('#editorTabList .nav-link').removeClass('active');
        $('[data-tabName="'+editorType+'"]').addClass('active');
        $('#editorTabContent .tab-pane').removeClass('active');
        $('#editor-'+editorType).addClass('active');
        $('#editor-'+editorType).addClass('active');
        
        $('.template-card').hide();
        $('.template-card-'+editorType).show();
        
        $('#selectEditorContainer').slideUp();
        $('#selectTemplateContainer').removeClass('d-none').slideDown();
        
        emailSettings.activeEditor = editorType;
        printSettings.activeEditor = 'tiny';
    });
</script>
@endpush
