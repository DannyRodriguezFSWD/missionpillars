{{ Form::model($customField, ['route' => ['settings.custom-fields.update', array_get($customField, 'id')], 'method' => 'PUT', 'name' => 'custom-field-edit-form']) }}

{{ Form::hidden('uid', Crypt::encrypt(array_get($customField, 'id'))) }}

<input type="hidden" name="type" class="form-control" value="{{ array_get($customField, 'type') }}">

<div class="form-group">
    <label class="col-form-label">@lang('Label') <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ array_get($customField, 'name') }}">
</div>

@if (array_get($customField, 'type') === 'select' || array_get($customField, 'type') === 'multiselect')
<div class="form-group">
    <label class="col-form-label">@lang('Pick list values') <span class="text-danger">*</span></label>
    <p class="small text-muted mb-0">Enter one per line</p>
    <textarea name="pick_list_values" class="form-control" rows="3">{{ $options }}</textarea>
</div>
@endif

<div class="form-group">
    <label class="col-form-label">@lang('Section') <span class="text-danger">*</span></label>
    <select name="custom_field_section_id" class="form-control">
        @foreach ($sections as $section)
        <option value="{{ array_get($section, 'id') }}" @if (array_get($section, 'id') === array_get($customField, 'custom_field_section_id')) 'selected' @else '' @endif>{{ array_get($section, 'name') }}</option>
        @endforeach
    </select>
</div>

{{ Form::close() }}

{{ Form::open(['route' => ['settings.custom-fields.destroy', array_get($customField, 'id')], 'method' => 'DELETE', 'name' => 'custom-field-delete-form']) }}
{{ Form::hidden('uid', Crypt::encrypt(array_get($customField, 'id'))) }}
{{ Form::close() }}
