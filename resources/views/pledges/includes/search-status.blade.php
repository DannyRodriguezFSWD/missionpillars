<div class="modal fade" id="search-status-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['transactions.search', 'status'], 'method' => 'get']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('status', __('Status')) }}
                    {{ Form::select('status', ['complete' => __('complete'), 'pending' => __('pending'), 'failed' => __('failed')], null, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submit">@lang('Search')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">

    $(function () {
        $('#search-status-modal').on('shown.coreui.modal', function (e) {
            $('select[name="status"]', this).focus();
        });
    });

</script>
@endpush