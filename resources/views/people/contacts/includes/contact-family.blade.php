@if (array_get($contact, 'family'))
<div class="card">
    <div class="card-body">
        <h5 class="card-title">
            @lang('Family:') {{ array_get($contact, 'family.name') }}
        </h5>

        <div class="row">
            <div class="col-md-4">
                <img src="{{ array_get($contact, 'family.family_image_src') }}" class="img-fluid rounded-lg" />
            </div>
            <div class="col-md-8">
                <div class="row">
                    @if ($contact->family->contacts()->count() > 0)
                        @foreach ($contact->family->contacts as $relative)
                        <div class="col-12 mb-2">
                            <div class="d-table">
                                <span class="d-table-cell">
                                    <img src="{{ $relative->profile_image_src }}" class="img-fluid img-thumbnail rounded-circle" style="width: 50px;" alt="{{ $relative->full_name }}" />
                                </span>
                                <span class="d-table-cell align-middle pl-2">
                                    {!! $relative->full_name_link !!}
                                </span>
                                <span class="d-table-cell align-middle pl-2">
                                    <small class="text-muted">
                                        {{ array_get($relative, 'family_position') }}
                                    </small>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <em>@lang('There are no contacts in this family')</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer text-center">
        @can('update', $contact)
            <button class="btn btn-primary" data-toggle="modal" data-target="#edit-family-modal">
                <i class="fa fa-edit"></i> @lang('Edit Family')
            </button>
        @endcan
    </div>
</div>
@else
<div class="card">
    <div class="card-body">
        <h5 class="card-title">
            @lang('Family Info')
        </h5>

        <div class="row">
            <div class="col-12">
                <em>@lang('This contact is not in a family').</em>
            </div>
        </div>
    </div>

    <div class="card-footer text-center">
        @can('update', $contact)
            <button class="btn btn-primary" data-toggle="modal" data-target="#search-family-modal">
                <i class="fa fa-plus"></i> @lang('Add To Family')
            </button>
        @endcan
    </div>
</div>
@endif

@if (array_get($contact, 'type') === 'person')
@include ('people.families.includes.search-modal')
@endif

@if (array_get($contact, 'family'))
@include ('people.families.includes.edit-modal')
@include ('people.families.includes.add-contact-to-family-modal', ['family' => array_get($contact, 'family')])
@endif
