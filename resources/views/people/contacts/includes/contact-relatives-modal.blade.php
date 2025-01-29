<div class="modal fade" id="contact-relatives-modal" tabindex="-1" role="dialog" aria-labelledby="contactRelativesEditRelationModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" data-modal-title="true"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="@if (array_get($contact, 'type') === 'organization') d-none @endif">
                            <h3><label for="contact_relationship">{{ array_get($contact, 'full_name') }} @lang('is the'):</label></h3>
                            <div class="form-group">
                                {{ Form::select('contact_relationship', $relationships, null, ['class' => 'form-control']) }}
                            </div>
                            <div class="d-none" data-show-if-contact-relationship-other="true">
                                <div class="form-group">
                                    {{ Form::label('contact_relationship_other', __('Add your custom relation (optional)')) }}
                                    {{ Form::text('contact_relationship_other', null, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <h3>@lang('Of')</h3>
                        </div>
                        <p class="font-weight-bold d-none" id="relativeNameContianer"></p>
                        <div id="people-search-with-create2" class="d-none">
                            <label>@lang('Find Contact')</label>
                            <people-search-with-create2
                                :on_save_contact="true"
                                :hide_title="true"
                                :create_contact_modal_id="'create-new-contact-modal2'"
                            ></people-search-with-create2>
                        </div>
                        <div class="form-group">
                            @if (array_get($contact, 'type') === 'organization')
                                {{ Form::label('relative_relationship', __('Role')) }}
                            @else
                                <label for="contact_relationship">@lang('Who is') {{ array_get($contact, 'full_name') }}'s:</label>
                            @endif
                            {{ Form::select('relative_relationship', $relationships, null, ['class' => 'form-control']) }}
                        </div>
                        <div class="d-none" data-show-if-relative-relationship-other="true">
                            <div class="form-group">
                                {{ Form::label('relative_relationship_other', __('Add your custom relation (optional)')) }}
                                {{ Form::text('relative_relationship_other', null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>
                    {{ Form::hidden('relative_id') }}
                    {{ Form::hidden('relative_up') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-create-relation="true" onclick="storeRelation();">@lang('Add')</button>
                <button type="button" class="btn btn-primary" data-edit-relation="true" onclick="saveRelation();">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function storeRelation() {
        customAjax({
            url: '{{ route('contacts.relatives.add', array_get($contact, 'id')) }}',
            data: {
                relative_id: $('#contact-relatives-modal [name="contact_id"]').val(),
                contact_relationship: $('#contact-relatives-modal [name="contact_relationship"]').val(),
                relative_relationship: $('#contact-relatives-modal [name="relative_relationship"]').val(),
                contact_relationship_other: $('#contact-relatives-modal [name="contact_relationship_other"]').val(),
                relative_relationship_other: $('#contact-relatives-modal [name="relative_relationship_other"]').val()
            },
            success: function (response) {
                Swal.fire('Success!', response.message,'success');
                window.location.reload();
            },
            error: function (e) {
                if (e.responseJSON) {
                    Swal.fire('Validation Error', parseResponseJSON(e.responseJSON), 'error');
                }
            }
        });
    }
    
    function saveRelation () {
        customAjax({
            url: '{{ route('contacts.relatives.update') }}',
            data: {
                contact_id: $('#contact-relatives-modal [name="contact_id"]').val(),
                relative_id: $('#contact-relatives-modal [name="relative_id"]').val(),
                contact_relationship: $('#contact-relatives-modal [name="contact_relationship"]').val(),
                relative_relationship: $('#contact-relatives-modal [name="relative_relationship"]').val(),
                relative_up: $('#contact-relatives-modal [name="relative_up"]').val(),
                contact_relationship_other: $('#contact-relatives-modal [name="contact_relationship_other"]').val(),
                relative_relationship_other: $('#contact-relatives-modal [name="relative_relationship_other"]').val()
            },
            success: function (response) {
                Swal.fire('Success!', response.message,'success');
                window.location.reload();
            }
        });
    }
    
    $('#contact-relatives-modal [name="contact_relationship"]').change(function () {
        if ($(this).val() === 'Other') {
            $('[data-show-if-contact-relationship-other="true"]').removeClass('d-none');
        } else {
            $('[data-show-if-contact-relationship-other="true"]').addClass('d-none');
        }
    });
    
    $('#contact-relatives-modal [name="relative_relationship"]').change(function () {
        if ($(this).val() === 'Other') {
            $('[data-show-if-relative-relationship-other="true"]').removeClass('d-none');
        } else {
            $('[data-show-if-relative-relationship-other="true"]').addClass('d-none');
        }
    });
</script>

<script src="{{ asset('js/people-search-with-create2.js') }}"></script>
@endpush
