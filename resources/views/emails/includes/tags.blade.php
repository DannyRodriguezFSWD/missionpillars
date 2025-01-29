<div class="modal fade" id="tags" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Tags')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="height: 65vh; overflow: auto;">
                <div class="row">
                    <div class="col-sm-12">
                        <ol class="tree">
                            <?php printMapTagEvent($tree); ?>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#new-tag">@lang('New tag')</button>
                <button type="submit" disabled="" id="button-select-tag" class="btn btn-primary disabled" data-dismiss="modal">@lang('Select tag')</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">@lang('Close')</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="new-tag" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('New tag')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body" style="height: 65vh; overflow: auto;">
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
                <button type="button" id="save-tag" class="btn btn-success" data-dismiss="modal">@lang('Save tag')</button>
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">@lang('Cancel')</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>