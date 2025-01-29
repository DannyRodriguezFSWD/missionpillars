<div class="modal fade" id="has-to-fill-form-modal-{{ array_get($ticket, 'id') }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Check In')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Optionally to check in into this event, contact must fill attached form.')</p>
                <p>@lang('Do you want to proceed')?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-dismiss="modal">@lang('No')</button>
                @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('forms.share', [array_get($registry, 'event.template.form.uuid'), 'cid' => array_get($registry, 'contact.id'), 'ticket_id' => array_get($ticket, 'id')]), 'caption' => 'Yes', 'form' => true])
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
