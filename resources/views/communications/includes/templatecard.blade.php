<div class="col-xl-3 col-lg-6 col-sm-6 template-card template-card-{{ $template->editor_type }}">
    <div class="template-container">
        <p class="h5 text-center text-truncate" title="{{ $template->name }}">{{ $template->name }}</p>
        <div class="template-preview-container">
            {!! $template->content !!}
        </div>
        <div class="template-buttons custom-overlay d-none">
            @if($template->tenant_id)
            <button class="btn btn-danger mt-2 mr-2 pull-right delete-template" data-toggle="tooltip" title="Delete this template" data-template-id="{{ $template->id }}" onclick="deleteTemplate(this);"><i class="fa fa-trash"></i></button>
            @endif
            <button type="button" class="btn btn-light rounded-xl py-2 font-weight-bold btn-editor-tempalte preview-template" data-template-id="{{ $template->id }}" style="top: 65%;" onclick="previewTemplate(this);"><i class="fa fa-eye"></i> @lang('Preview')</button>
            <button type="button" class="btn btn-warning rounded-xl py-2 font-weight-bold btn-editor-tempalte select-template" data-template-id="{{ $template->id }}" onclick="selectTemplate(this);">@lang('Use this template')</button>
        </div>
    </div>
</div>