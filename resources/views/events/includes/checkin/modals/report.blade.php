<div class="modal fade" id="checkinReportModal" tabindex="-1" role="dialog" aria-labelledby="checkinReportModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Checkin Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h4>Choose how you would like to print</h4>
                
                <div class="row">
                    <div class="col-12">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="checkinReportTypeAll" name="checkin_report_type" value="all" class="custom-control-input">
                            <label class="custom-control-label" for="checkinReportTypeAll">All attendees</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="checkinReportTypeGroup" name="checkin_report_type" value="group" class="custom-control-input">
                            <label class="custom-control-label" for="checkinReportTypeGroup">Divided by group</label>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info" data-show="all" style="display: none;">
                            The report will include all contacts that have signed up for the event.
                        </div>
                        
                        <div class="row" data-show="group" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <p class="mb-0">The report will include all contacts that are in the groups you have selected, separated by group.</p>
                                    <p class="mb-0">If a contact is part of multiple groups, they will show in each group.</p>
                                    <p class="mb-0">Contacts that have signed up but are not in any group will show separately.</p>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="report_groups">Select Small Groups</label>

                                    <div style="overflow-y: auto; max-height: 230px;">
                                        @foreach($groups as $group)
                                        <div class="form-check">
                                            <input class="form-check-input group-checkbox" type="checkbox" value="" id="report_group_{{ array_get($group, 'id') }}" name="report_group_{{ array_get($group, 'id') }}" data-groupid="{{ array_get($group, 'id') }}">
                                            <label class="form-check-label" for="report_group_{{ array_get($group, 'id') }}">
                                                {{ array_get($group, 'name') }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printCheckinReport()">Print</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
