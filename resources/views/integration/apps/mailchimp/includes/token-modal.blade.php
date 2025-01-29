<div class="modal fade" id="mailchimp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-info modal-lg" role="document">
        {{ Form::open(['route' => 'integrations.store']) }}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Mailchimp')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('API_KEY', 'API Key') }}
                    {{ Form::text('API_KEY', null, ['class' => 'form-control']) }}
                    {{ Form::hidden('service', 'Mailchimp') }}
                    {{ Form::hidden('description', 'Email plattform integration') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Save')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>