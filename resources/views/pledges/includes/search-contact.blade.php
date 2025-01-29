<div class="modal fade" id="search-contact-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['pledges.search', 'contact'], 'method' => 'get']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('keyword', __("Contact's Name")) }}
                    {{ Form::text('keyword', null, ['class' => 'form-control', 'placeholder' => "Contact's Name", 'autocomplete' => 'Off']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('email', __("Contact's email")) }}
                    {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => "Contact's Email", 'autocomplete' => 'Off']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('promised_pay_date', __("Promised Pay Date")) }}
                    {{ Form::text('promised_pay_date', null, ['class' => 'form-control datepicker', 'required' => true]) }}
                </div>
                <!--
                <div class="form-group">
                    {{ Form::label('start', __("Transaction start date")) }}
                    {{ Form::text('start', null, ['class' => 'form-control datepicker', 'autocomplete' => 'Off', 'readonly' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::label('end', __("Transaction end date")) }}
                    {{ Form::text('end', null, ['class' => 'form-control datepicker', 'autocomplete' => 'Off', 'readonly' => true]) }}
                </div>
                -->
                <div class="form-group">
                    {{ Form::label('status', __('Status')) }}
                    {{ Form::select('status', ['all' => __('All'), 'complete' => __('Complete'), 'pending' => __('Pending'), 'failed' => __('Failed'), 'refunded' => __('Refunded')], null, ['class' => 'form-control']) }}
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

        $('.datepicker').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true
        });

    });

</script>
@endpush