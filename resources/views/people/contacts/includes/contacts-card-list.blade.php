@foreach ($contacts as $contact)
    <div class="col-lg-3 col-md-4 col-sm-6">
        @include('people.contacts.includes.contact-card', $contact)
    </div>
@endforeach

@include ('people.families.includes.info-modal')
