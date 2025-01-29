@if ($contact)
<div class="d-table">
    <span class="d-table-cell">
        <img src="{{ $contact->profile_image_src }}" class="img-fluid img-thumbnail rounded-circle" style="width: 50px;" alt="{{ $contact->full_name }}" />
    </span>
    <span class="d-table-cell align-middle pl-2">
        {!! $contact->full_name_email_link !!}
    </span>
</div>
@endif