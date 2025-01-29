<div class="modal fade" id="search-contact-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['transactions.search', 'contact'], 'method' => 'get']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">

                    {{ Form::label('keyword', "Contact's Name") }}
                    {{ Form::text('keyword', null, ['class' => 'form-control', 'required' => true, 'placeholder' => "Contact's Name", 'autocomplete' => 'Off']) }}
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
        $('#search-contact-modal').on('shown.coreui.modal', function (e) {
            $('input[type=text]', this).focus();
        });
    });

</script>
@endpush