<div class="row">
    <div class="col-sm-10">
        @if(is_null($list))
        <h3>@lang('Create New List')</h3>
        @else
        <h3>@lang('Edit List')</h3>
        @endif
    </div>
    <div class="col-sm-2 text-right pb-2">
        <div class="" id="floating-buttons">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-edit"></i>
                @lang('Save')
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group {{$errors->has('name') ? 'has-danger':''}}">
            <span class="text-danger">*</span> 
            {{ Form::label('name', __("List's name")) }}
            @if ($errors->has('name'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('name') }}</strong></small>
            </span>
            @endif
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Untitled List'), 'required' => true, 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <span class="text-danger">*</span> 
            {{ Form::label('permission_reminder', 'Permission Reminder') }}
            <p>
                <small class="text-primary">
                    * @lang("A permission reminder makes it clear in your campaign where your recipients signed up for the list. In some cases, it can also help prevent you from mistakenly getting reported or blacklisted as a spammer.")
                </small>
            </p>
            @if ($errors->has('permission_reminder'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('permission_reminder') }}</strong></small>
            </span>
            @endif
            {{ Form::textarea('permission_reminder', null, ['class' => 'form-control', 'placeholder' => 'Permission Reminder', 'required' => true, 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h5>@lang('Select tags that will be part of list')</h5>
        <ol class="tree">
            <?php printFoldersTree($tree, $in); ?>
        </ol>
    </div>
    <div class="col-sm-6">
        <h5>@lang("Select tags that won't be part of list")</h5>
        <ol class="tree">
            <?php printFoldersTreeNot($tree, $not); ?>
        </ol>
    </div>
</div>