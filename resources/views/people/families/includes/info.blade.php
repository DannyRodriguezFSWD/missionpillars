<div class="row text-center mb-4 border-bottom">
    <div class="col-12 mx-0">
        <img src="{{ $family->family_image_src }}" class="img-fluid rounded-lg" style="max-height: 300px;" />
    </div>
</div>

@foreach ($family->contacts as $contact)
    <div class="row pl-5">
        <div class="col-12 mb-2">
            <div class="d-table">
                <span class="d-table-cell">
                    <img src="{{ $contact->profile_image_src }}" class="img-fluid img-thumbnail rounded-circle" style="width: 50px;" alt="{{ $contact->full_name }}" />
                </span>
                <span class="d-table-cell align-middle pl-2">
                    {!! $contact->full_name_link !!}
                </span>
                <span class="d-table-cell align-middle pl-2">
                    <small class="text-muted">
                        {{ array_get($contact, 'family_position') }}
                    </small>
                </span>
            </div>
        </div>
    </div>
@endforeach