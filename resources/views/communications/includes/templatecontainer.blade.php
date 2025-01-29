<div class="row bg-light p-3">
    <div class="col-12">
        <h4 class="mt-1">@lang('Global templates')</h4>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6">
        <p class="h5 text-center text-truncate" title="Blank">Blank</p>
        <img class="img-responsive img-fit-cover template-preview-container" src="{{ url('/img/communications/blank_template.jpg') }}" />
        <button type="button" class="btn btn-warning rounded-xl py-2 font-weight-bold btn-editor-tempalte select-template" data-template-id="{{ $content_templates->where('tenant_id', null)->where('editor_type', 'topol')->where('name', 'Blank')->first()->id }}" onclick="selectTemplate(this);">@lang('Start from scratch')</button>
    </div>

    @foreach ($content_templates->where('tenant_id', null)->where('name', '!=', 'Blank') as $template)
    @include('communications.includes.templatecard', $template)
    @endforeach

    <div class="col-12 mt-1">
        <h4>@lang('Your custom templates')</h4>
    </div>

    @foreach ($content_templates->where('tenant_id','!=', null) as $template)
    @include('communications.includes.templatecard', $template)
    @endforeach
</div>