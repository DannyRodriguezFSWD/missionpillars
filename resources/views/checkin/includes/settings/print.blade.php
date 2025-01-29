<div class="col-md-12 promote_togglable_div" id="print-settings">
    <div class="alert alert-info">
        <h4 class="mb-0" id="settingsTitle">
            Finally adjust how to print your tags
        </h4>
    </div>
    
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title font-weight-bold inline_block mb-0">Print Settings</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xl-6">
                    <p class="mb-0">Choose what shows on the print</p>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="checkin_print_info" value="name">
                        <label class="form-check-label">
                            Name only
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="checkin_print_info" value="name_and_details">
                        <label class="form-check-label">
                            Name and details
                        </label>
                    </div>

                    <div id="printDetialsContainer" style="display: none;">
                        <p class="mt-2 mb-0">Choose what details to show</p>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="parent" id="checkin_details_parent" name="checkin_details_parent">
                            <label class="form-check-label" for="checkin_details_parent">
                                Name of parent
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="grade" id="checkin_details_grade" name="checkin_details_grade">
                            <label class="form-check-label" for="checkin_details_grade">
                                Grade
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="note" id="checkin_details_note" name="checkin_details_note">
                            <label class="form-check-label" for="checkin_details_note">
                                Child checkin note
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="phone" id="checkin_details_phone" name="checkin_details_phone">
                            <label class="form-check-label" for="checkin_details_phone">
                                Phone
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="parent_phone" id="checkin_details_parent_phone" name="checkin_details_parent_phone">
                            <label class="form-check-label" for="checkin_details_parent_phone">
                                Parent's phone
                            </label>
                        </div>
                    </div>
                    
                    <p class="mb-0 mt-2">Print Tags</p>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="print_tags" value="2">
                        <label class="form-check-label">
                            Print two tags
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="print_tags" value="1">
                        <label class="form-check-label">
                            Print one tag only
                        </label>
                    </div>
                    <!--TODO add option to not print-->
                    <!--<div class="form-check">
                        <input class="form-check-input" type="radio" name="print_tags" value="0">
                        <label class="form-check-label">
                            Do not print name tags
                        </label>
                    </div>-->

                    <button type="button" class="btn btn-primary mt-3" onclick="savePrintSettings()">
                        Save Changes
                    </button>
                </div>
                
                <div class="col-lg-12 col-md-12 col-xl-6">
                    <div class="card" id="printSettingsPreview">
                        <div class="card-header">
                            <h5 class="mb-0">Preview</h5>
                        </div>
                        <div class="card-body" style="height: 180px;">
                            <h5>John Doe</h5>
                            <div data-print-info="checkin_print_info" data-value="name_and_details">
                                <p class="mb-0" data-print-info="checkin_details_parent" data-value="parent">Parent: Jim Doe</p>
                                <p class="mb-0" data-print-info="checkin_details_grade" data-value="grade">Grade: 5</p>
                                <p class="mb-0" data-print-info="checkin_details_note" data-value="note">Allergic to peanuts, no peanut butter</p>
                                <p class="mb-0" data-print-info="checkin_details_phone" data-value="phone">Phone: (234) 567-8901</p>
                                <p class="mb-0" data-print-info="checkin_details_parent_phone" data-value="parent_phone">Parent's phone: (345) 678-9012</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
