<div class="row" data-family-contact="true">
    <div class="col-12 mb-2">
        <div class="d-table">
            <span class="d-table-cell">
                <img src="{{ $contact->profile_image_src }}" class="img-fluid img-thumbnail rounded-circle" style="width: 50px;" alt="{{ $contact->full_name }}" />
            </span>
            <span class="d-table-cell align-middle pl-2">
                {!! $contact->full_name_link !!}
            </span>
            <span class="d-table-cell align-middle pl-2">
                <small class="text-muted" data-family-position="true">
                    {{ array_get($contact, 'family_position') }}
                </small>
            </span>
        </div>
        
        <span class="position-absolute" style="top: 1em; right: 2em;">
            <i class="fa fa-edit text-warning cursor-pointer mt-1 mr-2" title="Edit family position" data-family-position="{{ array_get($contact, 'family_position') }}" onclick="changeFamilyPosition(this, {{ array_get($contact, 'id') }})"></i>
            <i class="fa fa-trash text-danger cursor-pointer" title="Remove from family" onclick="removeFromFamily(this, {{ array_get($contact, 'id') }})"></i>
        </span>
    </div>
</div>
