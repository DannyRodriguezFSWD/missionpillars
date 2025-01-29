<h5 class="mb-4">@lang('Family Info')</h5>

<div class="row">
    <div class="col-md-6">
        <div id="family-search-with-create">
            <label>@lang('Family')</label>
            <family-search-with-create
                :on_save_contact="true"
                :hide_title="true"
                :family_id="{{ array_get($contact, 'family_id', 0) }}"
                :family="{{ json_encode(['id' => array_get($contact, 'family_id', 0), 'label' => array_get($contact, 'family.name')]) }}"
            ></family-search-with-create>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group {{$errors->has('family_position') ? 'has-danger':''}}">
            {{ Form::label('family_position', __('Family Position')) }}
            {{ Form::select('family_position', $familyPositions, null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/family-search-with-create.js') }}"></script>
@endpush
