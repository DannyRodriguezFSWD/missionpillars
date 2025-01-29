<div class="modal fade" id="folderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('New folder')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route'=>'folders.store']) }}
            {{-- Form::hidden('parent', $root->id, ['id' => 'parent']) --}}

            @if( isset($tags) )
            {{ Form::hidden('type', 'TAGS') }}
            @elseif( isset($groups) )
            {{ Form::hidden('type', 'GROUPS') }}
            @endif

            {{ Form::hidden('uid',  Crypt::encrypt($root->id), ['id' => 'uid']) }}
            @if( isset($contact) )
                {{ Form::hidden('cid',  Crypt::encrypt($contact->id), ['id' => 'cid']) }}
            @endif
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('folder') }}
                    {{ Form::text('folder', null, ['placeholder' => __('Enter new folder name'), 'class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('parent', 'Folder') }}
                    {{ Form::select('parent', $folderDropdown, $root->id, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">@lang('Save folder')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="edit-folder-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Edit folder')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('folder') }}
                    {{ Form::text('folder', null, ['placeholder' => __('Enter new folder name'), 'class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('parent', 'Folder') }}
                    {{ Form::select('parent', $folderDropdown, $root->id, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                <div id="alert-message" class="alert alert-danger">@lang("Select a diferent folder")</div>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-update-folder" class="btn btn-success">@lang('Save folder')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="delete-folder-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Delete folder')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p data-msg="@lang('are you sure you want to delete :folder: and all its content?')"></p>
                <small>@lang("This action can't be undone")</small>
            </div>
            <div class="modal-footer">
                <button id="button-delete-folder" type="button" class="btn btn-warning" data-dismiss="modal">@lang('Delete folder')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>