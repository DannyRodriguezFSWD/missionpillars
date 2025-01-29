<div class="modal fade" id="search-range-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['transactions.search', 'range'], 'method' => 'get']) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('min', __('Start Date')) }}
                    <div class="input-group mb-3 date {{$errors->has('dob') ? 'has-danger has-feedback':''}}">
                        {{ Form::text('min', isset($min) ? null : date('Y-m-d'), ['class' => 'form-control datepicker', 'placeholder' => __('Start Date'), 'required' => true, 'readonly' => true]) }}
                        <span class="input-group-addon start"><i class="icon-calendar"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('max', __('End Date')) }}
                    <div class="input-group mb-3 date {{$errors->has('dob') ? 'has-danger has-feedback':''}}">
                        {{ Form::text('max', isset($min) ? null : date('Y-m-d'), ['class' => 'form-control datepicker', 'placeholder' => __('End Date'), 'required' => true, 'readonly' => true]) }}
                        <span class="input-group-addon end"><i class="icon-calendar"></i></span>
                    </div>
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
        $('.date .min').on('click', function (e) {
            $('input[name="min"]').focus();
        });
        $('.date .max').on('click', function (e) {
            $('input[name="max"]').focus();
        });
    });

</script>
@endpush