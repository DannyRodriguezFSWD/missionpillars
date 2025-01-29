<ul id="editorTabList" class="nav nav-tabs mt-3 show-email" role="tablist">
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
        @include('communications.includes.editors.topol')
    </div>
    <div class="tab-pane" id="editor-tiny" role="tabpanel">
        @include('communications.includes.editors.tiny')
    </div>
</div>
