<div class="modal fade" id="has-to-buy-tickets-modal-{{ array_get($registry, 'id') }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Check In')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('In order to check in into this event, contact must buy tickets.')</p>
                <p>@lang('Do you want to proceed')?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-dismiss="modal">@lang('No')</button>
                @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('events.purchase.tickets', [array_get($registry, 'event.id'), 'c' => array_get($registry, 'contact.id'), 'r' => array_get($registry, 'id'), 'nt' => true]), 'caption' => 'Yes', 'form' => true])
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
