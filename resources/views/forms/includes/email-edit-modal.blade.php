<div class="modal fade" id="formEmailModal" tabindex="-1" role="dialog" aria-labelledby="formEmailModal" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">@lang('Edit Email')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="editorTabList" class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#editor-topol" role="tab" data-tabName="topol">
                            <i class="fa fa-mouse-pointer"></i> Drag and drop editor
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#editor-tiny" role="tab" data-tabName="tiny">
                            <i class="fa fa-edit"></i> Simple editor
                        </a>
                    </li>
                </ul>

                <div id="editorTabContent" class="tab-content">
                    <div class="tab-pane active" id="editor-topol" role="tabpanel">
                        @include('forms.includes.editors.topol')
                    </div>
                    <div class="tab-pane" id="editor-tiny" role="tabpanel">
                        @include('forms.includes.editors.tiny')
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveFormEmail();">Save changes</button>
            </div>
        </div>
    </div>
</div>
