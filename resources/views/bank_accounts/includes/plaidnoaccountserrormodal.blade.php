<div id="plaidNoAccountsErrorModal" class="modal fade" tabindex="-1" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg plaid-error-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-1 text-dark">@lang('Error: No bank accounts found')</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fa fa-exclamation-triangle fa-4x text-warning"></i>
                </div>

                <div class="lead text-justify">
                    <p>@lang('Sorry, it seems Plaid was not able to obtain any data from your banking institution.')<p>
                    <p>@lang('While our team cannot control this, we can let them know they have an error.')</p>
                    <br />
                    <p>@lang('Please send an email to') <a href="mailto:{{ env('APP_CUSTOMER_SERVICE_EMAIL') }}" class="bold">{{ env('APP_CUSTOMER_SERVICE_EMAIL') }}</a> @lang("including the name of the bank you were trying to log into and we'll reach out to them.")</p>
                    <br />
                    <p>@lang('In the mean-time, please try a different account.')</p>
                    <br />
                    <p>@lang('Thanks!')</p>
                </div>

                <button class="btn btn-primary mt-4" id="plaidTryAgain" data-dismiss="modal">
                    <i class="fa fa-repeat"></i> @lang('Try Again')
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>
