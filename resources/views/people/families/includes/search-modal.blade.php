<div class="modal fade" id="search-family-modal" tabindex="-1" role="dialog" aria-labelledby="searchFamilyModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search Or Create A New Family')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div id="family-search-with-create">
                            <label>@lang('Family')</label>
                            <family-search-with-create
                                :on_save_contact="true"
                                :hide_title="true"
                            ></family-search-with-create>
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
                <button type="button" class="btn btn-primary" onclick="createFamily();">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function createFamily() {
        customAjax({
            url: '{{ route('contacts.update-family', array_get($contact, 'id')) }}',
            data: {
                family_id: $('#search-family-modal [name="family_id"]').val(),
                family_position: $('#search-family-modal [name="family_position"]').val()
            },
            success: function (response) {
                Swal.fire('Success!', response.message, 'success');
                window.location.reload();
            },
            error: function (e) {
                if (e.responseJSON) {
                    Swal.fire('Validation Error', parseResponseJSON(e.responseJSON), 'error');
                }
            }
        });
    }
</script>

<script src="{{ asset('js/family-search-with-create.js') }}"></script>
@endpush
