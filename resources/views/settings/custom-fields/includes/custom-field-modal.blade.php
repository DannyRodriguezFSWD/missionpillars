<div class="modal fade" id="custom-field-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="mb-0">@lang('Select Field Type')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route' => ['settings.custom-fields.store'], 'method' => 'POST']) }}
            <div class="modal-body">
                <div id="serach-custom-field-type">
                    <input type="text" name="search-custom-field" class="form-control" placeholder="Search Field" />
                    
                    <ul class="list-group mt-3 cursor-pointer" style="max-height: 40vh; overflow-y: auto;">
                        <li class="list-group-item list-group-item-action" data-type="text" data-type-label="Text">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    Aa
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Text</p>
                                    <p class="mb-0 text-muted">The text field can store up to 255 characters.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="textarea" data-type-label="Textarea">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <i class="fa fa-sticky-note-o"></i>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Textarea</p>
                                    <p class="mb-0 text-muted">The textarea field lets you store much bigger texts than text fields.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="integer" data-type-label="Number">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <u>123</u>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Number</p>
                                    <p class="mb-0 text-muted">The number field can have numeric values without any decimal points.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="decimal" data-type-label="Decimal">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <u>.12</u>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Decimal</p>
                                    <p class="mb-0 text-muted">The decimal field can have numeric values with two decimal points.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="date" data-type-label="Date">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Date</p>
                                    <p class="mb-0 text-muted">The date field lets you select date input using the calendar component.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="datetime" data-type-label="Date Time">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Date Time</p>
                                    <p class="mb-0 text-muted">The date time field lets you select a date and time information using calendar + time picker.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="select" data-type-label="Select">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <i class="fa fa-arrow-circle-o-down"></i>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Select</p>
                                    <p class="mb-0 text-muted">The field lets you define a list of options that will appear in the dropdown. You can select a single option from the list.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item list-group-item-action" data-type="multiselect" data-type-label="Multi Select">
                            <div class="row">
                                <div class="col-1 text-muted">
                                    <i class="fa fa-check-square-o"></i>
                                </div>
                                <div class="col-11">
                                    <p class="mb-1">Multi Select</p>
                                    <p class="mb-0 text-muted">The field lets you define a list of options that will appear in the dropdown. You can select multiple options from the list.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div id="custom-field-data" class="d-none">
                    <p class="mb-0">
                        New <b id="type-label-text"></b> Field <button type="button" class="btn btn-link" id="back-to-type-select">Change</button>
                    </p>
                    
                    <input id="custom-field-type" type="hidden" name="type" class="form-control">
                    
                    <div class="form-group">
                        <label class="col-form-label">@lang('Label') <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    
                    <div class="form-group d-none" id="pick-list-values">
                        <label class="col-form-label">@lang('Pick list values') <span class="text-danger">*</span></label>
                        <p class="small text-muted mb-0">Enter one per line</p>
                        <textarea name="pick_list_values" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-form-label">@lang('Section') <span class="text-danger">*</span></label>
                        <select name="custom_field_section_id" class="form-control">
                            @foreach ($sections as $section)
                            <option value="{{ array_get($section, 'id') }}">{{ array_get($section, 'name') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> @lang('Save')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('[name="search-custom-field"]').keyup(function () {
        var search = $(this).val();
        
        var allTypes = $('#serach-custom-field-type li');
        
        if (search) {
            allTypes.hide();
            
            allTypes.each(function (i, el) {
                $(el).hide();
                
                var type = $(el).data('type');
                
                if (type.includes(search)) {
                    $(el).show();
                }
            });
        } else {
            allTypes.show();
        }
    });
    
    $('#serach-custom-field-type li').click(function () {
        $('#serach-custom-field-type').addClass('d-none');
        $('#custom-field-data').removeClass('d-none');
        $('#custom-field-modal .modal-header h4').html('Add Custom Field');
        
        var type = $(this).data('type');
        var typeLabel = $(this).data('type-label');
        
        $('#custom-field-type').val(type);
        
        $('#type-label-text').html(typeLabel);
        
        if (type === 'select' || type === 'multiselect') {
            $('#pick-list-values').removeClass('d-none');
        } else {
            $('#pick-list-values').addClass('d-none');
        }
    });
    
    $('#back-to-type-select').click(function () {
        $('#custom-field-data').addClass('d-none');
        $('#serach-custom-field-type').removeClass('d-none');
        $('#custom-field-modal .modal-header h4').html('Select Field Type');
        $('[name="name"]').val('');
        $('[name="pick_list_values"]').val('');
        $('#custom-field-type').val('');
    });
    
    $('#custom-field-modal').on('hidden.coreui.modal', function () {
        $('#custom-field-data').addClass('d-none');
        $('#serach-custom-field-type').removeClass('d-none');
        $('#custom-field-modal .modal-header h4').html('Select Field Type');
        $('[name="name"]').val('');
        $('[name="pick_list_values"]').val('');
        $('#custom-field-type').val('');
        $('[name="search-custom-field"]').val('');
        $('#serach-custom-field-type li').show();
    });
</script>
@endpush
