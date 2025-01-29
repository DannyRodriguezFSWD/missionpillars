<div class="modal fade" id="tagModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('New tag')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route'=>'tags.store']) }}
            {{-- Form::hidden('parent', $root->id, ['id' => 'parent']) --}}
            {{ Form::hidden('uid',  Crypt::encrypt($root->id), ['id' => 'uid']) }}
            @if( isset($contact) )
                {{ Form::hidden('cid',  Crypt::encrypt($contact->id), ['id' => 'cid']) }}
            @endif
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('tag') }}
                    {{ Form::text('tag', null, ['placeholder' => __('Enter new tag name'), 'class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                @if ($errors->has('name'))
                <p class="alert alert-danger">{{ $errors->first('name') }}</p>
                @endif
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('parent', 'Folder') }}
                    {{ Form::select('parent', $folderDropdown, $root->id, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">@lang('Save tag')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="edit-tag-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Edit tag')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('tag') }}
                    {{ Form::text('tag', null, ['placeholder' => __('Enter new tag name'), 'class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span>
                    {{ Form::label('parent', 'Folder') }}
                    {{ Form::select('parent', $folderDropdown, $root->id, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="button-update-tag" class="btn btn-success">@lang('Save tag')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="delete-tag-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Delete tag')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p data-msg="@lang('are you sure you want to delete :tag:?')"></p>
                <small>@lang("This action can't be undone")</small>
            </div>
            <div class="modal-footer">
                <button id="button-delete-tag" type="button" class="btn btn-warning" data-dismiss="modal">@lang('Delete tag')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>