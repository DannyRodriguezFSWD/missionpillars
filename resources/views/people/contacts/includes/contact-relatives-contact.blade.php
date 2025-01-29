<div data-relationship-container="true">
    <div class="d-table">
        <span class="d-table-cell">
            <img src="{{ $relative->profile_image_src }}" class="img-fluid img-thumbnail rounded-circle" style="width: 50px;" alt="{{ $relative->full_name }}" />
        </span>
        <span class="d-table-cell align-middle pl-2">
            {!! $relative->full_name_link_short !!}
        </span>
        <span class="d-table-cell align-middle pl-2">
            <small class="text-muted" data-relationship="true">
                @if ($up)
                    {{ str_limit(array_get($relative, 'pivot.contact_relationship'), 10) }}
                @else
                    {{ str_limit(array_get($relative, 'pivot.relative_relationship'), 10) }}
                @endif
            </small>
            <small class="d-none" data-relationship-hidden="true">
                @if ($up)
                    {{ str_limit(array_get($relative, 'pivot.relative_relationship'), 10) }}
                @else
                    {{ str_limit(array_get($relative, 'pivot.contact_relationship'), 10) }}
                @endif
            </small>
        </span>
    </div>

    <span class="position-absolute" style="top: 1em; right: 0;">
        <i class="fa fa-edit text-warning cursor-pointer mt-1 mr-2" title="Edit relation" onclick="editRelation(this, {{ array_get($relative, 'id') }}, `{{ array_get($relative, 'full_name') }}`, {!! $up !!})"></i>
        <i class="fa fa-trash text-danger cursor-pointer" title="Remove relation" onclick="removeRelation({{ array_get($relative, 'id') }}, {!! $up !!})"></i>
    </span>
</div>