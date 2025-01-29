<div class="modal fade" id="edit-family-modal" tabindex="-1" role="dialog" aria-labelledby="editFamilyModal" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Manage Family') - {{ array_get($contact, 'family.name') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        @include ('people.families.includes.form', ['family' => array_get($contact, 'family')])
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="$('#family-form').submit();">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#search-family-modal">
                    <i class="fa fa-search"></i> @lang('Change Family')
                </button>
            </div>
        </div>
    </div>
</div>
