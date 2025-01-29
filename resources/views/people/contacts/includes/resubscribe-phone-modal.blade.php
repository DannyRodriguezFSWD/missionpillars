<div class="modal fade" id="resubscribe-phone-modal" tabindex="-1" role="dialog" aria-labelledby="resubscribePhoneModal" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Contact Resubscribe')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-0">
                    <p>{{ array_get($contact, 'full_name') }} has opted out from these phone numbers:<br> {{ array_get($contact, 'unsubscribed_from_phones') }}</p>
                    <p class="mb-0">If you want them to receive messages again from the above numbers, they will need to text the word <b>Start</b> to that phone number. 
                        <br>You will need to contact them outside the system and let them know.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>
