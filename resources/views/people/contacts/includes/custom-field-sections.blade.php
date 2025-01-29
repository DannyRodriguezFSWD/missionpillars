@foreach ($customFieldSections as $section)
<div class="card shadow-lg">
    <div class="card-body">
        <div class="custom-fields">
            <h5 class="mb-4">
                @if (array_get($section, 'name') === 'Default')
                @lang('Custom Fields')
                @else
                @lang(array_get($section, 'name'))
                @endif
            </h5>

            <div class="row">
                @foreach ($customFields as $field)
                    @if (array_get($field, 'custom_field_section_id') === array_get($section, 'id'))
                    <div class="col-md-6">
                        @include('people.contacts.includes.custom-field')
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach


