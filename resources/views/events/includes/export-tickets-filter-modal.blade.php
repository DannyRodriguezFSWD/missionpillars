<div class="modal fade" id="export-tickets-filter-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-file-excel-o"></i>
                    @lang('Export Tickets')
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('events.tickets.export', ['id' => 'all']) }}" method="GET" id="exportTicketsForm">
                    <div class="row">
                        <div class="form-group col-lg-6">
                            {{ Form::label('event_start', 'Event From Date') }}
                            {{ Form::text('event_start', date('Y-m-d', strtotime('-1 year', time())), ['class' => 'form-control datepicker readonly export-tickets-filter']) }}
                        </div>
                        <div class="form-group col-lg-6">
                            {{ Form::label('event_end', 'Event To Date') }}
                            {{ Form::text('event_end', date('Y-m-d', strtotime('+1 year', time())), ['class' => 'form-control datepicker readonly export-tickets-filter']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-6">
                            {{ Form::label('ticket_start', 'Ticket Purchased From Date') }}
                            {{ Form::text('ticket_start', null, ['class' => 'form-control datepicker readonly export-tickets-filter']) }}
                        </div>
                        <div class="form-group col-lg-6">
                            {{ Form::label('ticket_end', 'Ticket Purchased To Date') }}
                            {{ Form::text('ticket_end', null, ['class' => 'form-control datepicker readonly export-tickets-filter']) }}
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="$('#exportTicketsForm').submit()"><i class="fa fa-file-excel-o"></i> @lang('Export Tickets')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>
