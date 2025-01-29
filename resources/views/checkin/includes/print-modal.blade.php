<div class="modal fade" id="checkinPrintModal" tabindex="-1" role="dialog" aria-labelledby="checkinPrintModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Print Tags')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" placeholder="@lang('Search people by name or email')" id="searchContacts">
                        </div>
                    </div>
                </div>

                <div class="row" id="contactsTableContainer" style="overflow-y: auto; max-height: calc(100vh - 322px);">
                    <div class="col-12">
                        <table class="table table-striped" id="contactsTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Primary Contact')</th>
                                    <th>@lang('Grade')</th>
                                    <th>@lang('Note')</th>
                                    <th>@lang('Phone')</th>
                                    <th>@lang('Primary Contact Phone')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printTags()"><i class="fa fa-print"></i> @lang('Print')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>
