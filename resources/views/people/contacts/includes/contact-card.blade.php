<div class="card" style="min-height: 270px;">
    <div class="card-body">
        <p class="text-center">
            <img class="img-fluid img-thumbnail rounded-circle" style="width: 100px;" src="{{ $contact->profile_image_src }}" />
        </p>
        <h4>{!! $contact->full_name_link !!}</h4>
        @if ($contact->email_1)
        <p class="mb-0"><i class="fa fa-envelope text-warning"></i> {{ $contact->email_1 }}</p>
        @endif
        @if ($contact->cell_phone)
        <p class="mb-0"><i class="fa fa-phone text-info"></i> {{ $contact->cell_phone }}</p>
        @endif
        @if ($contact->full_address)
        <p class="mb-0"><i class="fa fa-map-marker text-success"></i> {{ $contact->full_address }}</p>
        @endif
        @if ($contact->family_id)
        <p class="mb-0 cursor-pointer text-info" onclick="showFamilyInfo({{ $contact->family_id }});">
            <i class="fa fa-home text-danger"></i> Family: {{ $contact->family->name }} <i class="fa fa-info-circle text-info"></i>
        </p>
        @endif
    </div>
</div>