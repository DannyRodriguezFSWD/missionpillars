<div class="modal fade" id="statement-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'print-mail.store', 'name' => 'print-form']) }}
            {{ Form::hidden('contact_id', array_get($contact, 'id')) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Print Statement')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                @includeIf('contributions.statements.includes.form')
            </div>
            <div class="modal-footer">
                <button id="pdf" type="button" class="btn btn-secondary">
                    <i class="fa fa-file-pdf-o"></i> @lang('Download as PDF')
                </button>
                <button id="print" type="submit" class="btn btn-primary">
                    <span class="fa fa-print"></span> @lang('Print')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>