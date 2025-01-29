<div class="modal fade" id="add-contact-to-family-modal" tabindex="-1" role="dialog" aria-labelledby="AddContactToFamilyModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Add People To') - {{ array_get($family, 'name') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div id="people-search-with-create">
                            <label>@lang('Find Contact')</label>
                            <people-search-with-create
                                :on_save_contact="true"
                                :hide_title="true"
                            ></people-search-with-create>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group {{$errors->has('family_position') ? 'has-danger':''}}">
                            {{ Form::label('family_position', __('Family Position')) }}
                            {{ Form::select('family_position', $familyPositions, null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="addContactToFamily();">@lang('Add')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addContactToFamily() {
        customAjax({
            url: '{{ route('families.add-contact', array_get($family, 'id')) }}',
            data: {
                contact_id: $('#add-contact-to-family-modal [name="contact_id"]').val(),
                family_position: $('#add-contact-to-family-modal [name="family_position"]').val()
            },
            success: function (response) {
                Swal.fire('Success!', response.message, 'success');
                $('#family-contacts-list').append(response.html);
                $('#add-contact-to-family-modal').modal('hide');
            },
            error: function (e) {
                if (e.responseJSON) {
                    Swal.fire('Validation Error', parseResponseJSON(e.responseJSON), 'error');
                }
            }
        });
    }
    
    function changeFamilyPosition(button, contactId) {
        let familyPositions = JSON.parse('{!! json_encode($familyPositions) !!}');
        let currentPosition = $(button).attr('data-family-position');
        
        let options = '';
        Object.keys(familyPositions).forEach(function(key) {
            if (key !== ' ') {
                options = options + '<option value="'+familyPositions[key]+'" '+(currentPosition === familyPositions[key] ? 'selected' : '')+'>'+familyPositions[key]+'</option>';
            }
        });
        
        Swal.fire({
            title: 'Update family position',
            type: 'question',
            html: '<select class="form-control w-50 mx-auto my-4" data-change-family-position-select="true">'+options+'</select>',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: 'Close',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return customAjax({
                    url: contactId+'/update-family-position',
                    data: {
                        family_position: $('[data-change-family-position-select="true"]').val()
                    },
                    success: function (response) {
                        $(button).attr('data-family-position', response.family_position);
                        $(button).parents('[data-family-contact="true"]').find('[data-family-position="true"]').html(response.family_position);
                        Swal.fire('Success!', response.message, 'success');
                    }
                });
            }
        });
    }
    
    function removeFromFamily(button, contactId) {
        Swal.fire({
            title: 'Remove this contact from this family?',
            type: 'question',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return customAjax({
                    url: contactId+'/update-family-position',
                    data: {
                        family_id: null,
                        family_position: null
                    },
                    success: function () {
                        $(button).parents('[data-family-contact="true"]').remove();
                        Swal.fire('Success!', 'Contact was removed from family', 'success');
                    }
                });
            }
        });
    }
</script>

<script src="{{ asset('js/people-search-with-create.js') }}"></script>
@endpush
