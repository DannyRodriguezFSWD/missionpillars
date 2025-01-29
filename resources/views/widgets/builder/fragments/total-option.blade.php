<div class="card-body" id="total-option">
    <div class="row">
        <div class="col-sm-12">
            <label>@lang('Show')</label>
            <div class="form-group">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-primary active period-option">
                        {{ Form::radio('period', 'current_year', true) }} @lang('Current year')
                    </label>
                    <label class="btn btn-primary period-option">
                        {{ Form::radio('period', 'current_month') }} @lang('Current month')
                    </label>
                    <label class="btn btn-primary period-option">
                        {{ Form::radio('period', 'date_range') }} @lang('Date range')
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row date_range">
        <div class="col-sm-12">
            <div class="form-group form-inline">
                {{ Form::label('from', __('From')) }}&nbsp;
                {{ Form::text('from', null, ['class' => 'form-control datepicker']) }}
                &nbsp;&nbsp;&nbsp;&nbsp;
                {{ Form::label('to', __('To')) }}&nbsp;
                {{ Form::text('to', null, ['class' => 'form-control datepicker']) }}
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                {{ Form::label('group_by', __('Group values by')) }}<br>
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-primary">
                        {{ Form::radio('group_by', 'days') }}
                        @lang('Days')
                    </label>
                    <label class="btn btn-primary">
                        {{ Form::radio('group_by', 'weeks') }}
                        @lang('Weeks')
                    </label>
                    <label class="btn btn-primary active">
                        {{ Form::radio('group_by', 'months', true) }} @lang('Months')
                    </label>
                    <label class="btn btn-primary">
                        {{ Form::radio('group_by', 'years') }} @lang('Years')
                    </label>
                    <label class="btn btn-primary transaction-option-group">
                        {{ Form::radio('group_by', 'device') }} @lang('Device')
                    </label>
                    <label class="btn btn-primary transaction-option-group">
                        {{ Form::radio('group_by', 'transaction_path') }} @lang('Transaction source')
                    </label>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                {{ Form::checkbox('include_last_year', true) }} @lang('Include last year comparison')
            </div>
        </div>
    </div>
</div>
<hr/>
@push('styles')
<style>
    .date_range, .transaction-option-group{ display: none; }
    .btn-group > .btn:focus, .btn-group > .btn:active, .btn-group > .btn.active, .btn-group-vertical > .btn:focus, .btn-group-vertical > .btn:active, .btn-group-vertical > .btn.active{
        z-index: initial;
    }
</style>
@endpush
@push('scripts')
<script>
    (function(){
        $('.period-option').on('click', function(e){
            var option = $(this).find('input[type="radio"]').val();
            if(option === 'date_range'){
                $('.date_range').fadeIn();
            }
            else{
                $('.date_range').fadeOut();
            }
        });
        
        $('select[name="what"]').on('change', function(e){
            var value = $(this).val();
            if(value === 'giving'){
                $('.transaction-option-group').show();
            }
        });
        
        $('input[name="group_by"]').on('change', function(){
            var checked = $(this).prop('checked');
            var value = $(this).val();
            if(checked && (value === 'device' || value === 'transaction_path')){
                $('input[name="include_last_year"]').prop('checked', false).parent().fadeOut();
            }
            else{
                $('input[name="include_last_year"]').parent().fadeIn();
            }
        });
        
    })();
</script>
@endpush