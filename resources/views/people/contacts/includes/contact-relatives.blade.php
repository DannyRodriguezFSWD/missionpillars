<div class="card">
    <div class="card-body">
        <h5 class="card-title">
            @if (array_get($contact, 'type') === 'organization')
                @lang('Organization Contacts')
            @else
                @lang('Other Relations')
            @endif
        </h5>

        <div class="row">
            @if ($contact->relatives()->count() > 0 || $contact->relativesUp()->count() > 0)
                @foreach ($contact->relatives as $relative)
                <div class="col-sm-6 mb-2">
                    @include ('people.contacts.includes.contact-relatives-contact', ['up' => false])
                </div>
                @endforeach

                @foreach ($contact->relativesUp as $relative)
                <div class="col-sm-6 mb-2">
                    @include ('people.contacts.includes.contact-relatives-contact', ['up' => true])
                </div>
                @endforeach
            @else
                <div class="col-12">
                    @if (array_get($contact, 'type') === 'organization')
                    <em>@lang('No contacts have been added for') {{ array_get($contact, 'full_name') }}</em>
                    @else
                    <em>@lang('No relatives have been set up for') {{ array_get($contact, 'full_name') }}</em>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card-footer text-center">
        @can('update', $contact)
            <button type="button" class="btn btn-primary" onclick="addRelation();">
                <i class="fa fa-plus"></i>  @lang('Add Relation')
            </button>
        @endcan
    </div>
</div>

@include ('people.contacts.includes.contact-relatives-modal')

@push('scripts')
<script>
    function addRelation() {
        let modal = $('#contact-relatives-modal');
        
        modal.find('#contact_relationship').val('');
        modal.find('#relative_relationship').val('');
        modal.find('[name="contact_id"]').val('');
        modal.find('#relativeNameContianer').addClass('d-none');
        modal.find('#people-search-with-create2').removeClass('d-none');
        modal.find('[data-create-relation="true"]').removeClass('d-none');
        modal.find('[data-edit-relation="true"]').addClass('d-none');
        modal.find('[data-modal-title="true"]').html('Add Relation');
        
        @if (array_get($contact, 'type') === 'organization')
            modal.find('#contact_relationship').val('Employer');
        @endif
        
        modal.modal('show');
    }
    
    function editRelation(button, relativeId, relativeName, up) {
        let modal = $('#contact-relatives-modal');
        let relationContainer = $(button).parents('[data-relationship-container="true"]');
        
        if (modal.find('#contact_relationship option[value="'+relationContainer.find('[data-relationship-hidden="true"]').html().trim()+'"]').length === 0) {
            modal.find('#contact_relationship').append('<option value="'+relationContainer.find('[data-relationship-hidden="true"]').html().trim()+'">'+relationContainer.find('[data-relationship-hidden="true"]').html().trim()+'</option>');
        }
        
        if (modal.find('#relative_relationship option[value="'+relationContainer.find('[data-relationship="true"]').html().trim()+'"]').length === 0) {
            modal.find('#relative_relationship').append('<option value="'+relationContainer.find('[data-relationship="true"]').html().trim()+'">'+relationContainer.find('[data-relationship="true"]').html().trim()+'</option>');
        }
        
        modal.find('#contact_relationship').val(relationContainer.find('[data-relationship-hidden="true"]').html().trim());
        modal.find('#relative_relationship').val(relationContainer.find('[data-relationship="true"]').html().trim());
        modal.find('[name="contact_id"]').val(up ? relativeId : {{ array_get($contact, 'id') }});
        modal.find('[name="relative_id"]').val(up ? {{ array_get($contact, 'id') }} : relativeId);
        modal.find('[name="relative_up"]').val(up ? 1 : 0);
        modal.find('#relativeNameContianer').removeClass('d-none').html(relativeName);
        modal.find('#people-search-with-create2').addClass('d-none');
        modal.find('[data-create-relation="true"]').addClass('d-none');
        modal.find('[data-edit-relation="true"]').removeClass('d-none');
        modal.find('[data-modal-title="true"]').html('Edit Relation');

        modal.modal('show');
    }
    
    function removeRelation(id, up) {
        let contactId = up ? id : {{ array_get($contact, 'id') }};
        let relativeId = up ? {{ array_get($contact, 'id') }} : id;
        
        Swal.fire({
            title: 'Delete this relation?',
            type: 'question',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return customAjax({
                    url: contactId+'/relatives/delete',
                    data: {
                        relative_id: relativeId
                    },
                    success: function (response) {
                        Swal.fire('Success!', response.message,'success');
                        window.location.reload();
                    }
                });
            }
        });
    }
</script>
@endpush
