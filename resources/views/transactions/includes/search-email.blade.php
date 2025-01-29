<div class="modal fade" id="search-email-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['transactions.search', 'email'], 'method' => 'get']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">

                    {{ Form::label('email', "Contact's Email") }}
                    {{ Form::email('email', null, ['class' => 'form-control', 'required' => true, 'placeholder' => "Contact's Email", 'autocomplete' => 'Off']) }}
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
        $('#search-email-modal').on('shown.coreui.modal', function (e) {
            $('input[type=email]', this).focus();
        });
    });

</script>
@endpush