{{ Form::model($section, ['route' => ['settings.custom-fields.update-section', array_get($section, 'id')], 'method' => 'PUT', 'name' => 'custom-field-section-edit-form']) }}

{{ Form::hidden('uid', Crypt::encrypt(array_get($section, 'id'))) }}

<div class="form-group">
    <label class="col-form-label">@lang('Name') <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ array_get($section, 'name') }}">
</div>

{{ Form::close() }}

{{ Form::open(['route' => ['settings.custom-fields.destroy-section', array_get($section, 'id')], 'method' => 'DELETE', 'name' => 'custom-field-section-delete-form']) }}
{{ Form::hidden('uid', Crypt::encrypt(array_get($section, 'id'))) }}
{{ Form::close() }}
