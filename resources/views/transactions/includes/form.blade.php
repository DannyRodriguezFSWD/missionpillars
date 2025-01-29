
<div class="row">
    <div class="col-sm-12">
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-primary {{ old('is_recurring') == 0 ? 'active' : '' }} is_recurring">
                <input type="radio" name="is_recurring" value="0" autocomplete="off" {{ old('is_recurring') == 0 ? 'checked' : '' }}> @lang('Single Transaction')
            </label>
            @if($create_pledge === 'true')
            <label class="btn btn-primary {{ old('is_recurring') == 1 ? 'active' : '' }} is_recurring">
                <input type="radio" name="is_recurring" value="1" autocomplete="off" {{ old('is_recurring') == 1 ? 'checked' : '' }}> @lang('Recurring Transaction')
            </label>
            @endif
        </div>
    </div>
</div>
<br/>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group {{$errors->has('contact_id') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('contact', __("Contact's Name")) }}
            {{ Form::text('contact', $contact, ['class' => 'form-control autocomplete', 'placeholder' => "Contact's Name", 'required' => true, 'autocomplete' => 'off']) }}
            {{ Form::hidden('contact_id', $cid) }}
            @if ($errors->has('contact_id'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('contact_id') }}</strong></small>
            </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        <div class="form-group">
            {{ Form::label('campaign_id', __('Fundraiser')) }}
            {{ Form::select('campaign_id', $campaigns, array_get($split, 'campaign_id'), ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-sm-5">
        <div class="form-group {{$errors->has('purpose_id') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('purpose_id', __('Purpose')) }}
            <select name="purpose_id" value="{{ array_get($split, 'purpose_id', array_get($purpose, 'id')) }}" class="form-control">
                @foreach ($charts as $p)
                    <option value="{{$p->id}}"{{ array_get($split, 'purpose_id', array_get($purpose, 'id')) == $p->id ?' selected':'' }}>{{$p->name}}</option>
                    @if (isset($p->childPurposes))
                        @foreach ($p->childPurposes as $child_purpose)
                            <option value="{{$child_purpose->id}}"{{ array_get($split, 'purpose_id', array_get($purpose, 'id')) == $child_purpose->id ?' selected':'' }}>{{$p->name}}\{{$child_purpose->name}}</option>
                        @endforeach
                    @endif
                @endforeach
            </select>
            @if ($errors->has('purpose_id'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('purpose_id') }}</strong></small>
            </span>
            @endif
        </div>
        @if($link_purposes_and_accounts)
            <p class="p-0 m-0"><i class="fa fa-info"></i> Did you know you can link purposes to income accounts?</p>
            <p>This makes it easier to manage you accounting entries, <a target="_blank" href="{{ route('purposes.index') }}">click here and start linking your purposes <i class="fa fa-external-link"></i></a></p>
        @endif
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('type', __('Transaction Type')) }}
            {{ Form::select('type', ['donation' => 'Donation', 'purchase' => 'Purchase'], array_get($split, 'type'), ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<hr/>
<div class="row recurring-row">
    <div class="col-sm-2">
        <div class="form-group {{$errors->has('billing_period') ? 'has-danger':''}}">
            {{ Form::label('billing_period', __('Frequency')) }}
            {{ Form::select('billing_period', $periods, array_get($split, 'transaction.template.billing_period', array_get($split, 'template.billing_period', 'Monthly') ), ['class' => 'form-control']) }}
            @if ($errors->has('billing_period'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('billing_period') }}</strong></small>
            </span>
            @endif
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group {{$errors->has('billing_cycles') ? 'has-danger':''}}">
            <!-- <span class="text-danger">*</span> --> {{ Form::label('billing_cycles', __('&nbsp;')) }}
            {{ Form::number('billing_cycles', array_get($split, 'transaction.template.billing_cycles', array_get($split, 'template.billing_cycles', 1) ), ['class' => 'form-control text-center calculate', 'min' => 1, 'step' => 1]) }}
            @if ($errors->has('billing_cycles'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('billing_cycles') }}</strong></small>
            </span>
            @endif
        </div>
    </div>

    <div class="col-sm-2">
        <label>&nbsp;</label>
        <input id="label" name="label" type="text" class="form-control" style="border: none;" readonly="true" value="Month"/>
    </div>

    <div class="col-sm-3">
        <div class="form-group {{$errors->has('billing_start_date') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('billing_start_date', __('Start Billing at')) }}
            {{ Form::text('billing_start_date', \Carbon\Carbon::createFromTimestamp(strtotime(array_get($split, 'transaction.template.billing_start_date', array_get($split, 'template.billing_start_date', \Carbon\Carbon::now()->toDateString()) )))->toDateString(), ['class' => 'form-control datepicker readonly']) }}
            @if ($errors->has('billing_start_date'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('billing_start_date') }}</strong></small>
            </span>
            @endif
        </div>
    </div>
    
    <div class="col-sm-3">
        <div class="form-group {{$errors->has('billing_end_date') ? 'has-danger':''}}">
            {{ Form::label('billing_end_date', __('End Billing at')) }}
            <div class="form-control" id="billing_end_date">
                {{ \Carbon\Carbon::createFromTimestamp(strtotime(array_get($split, 'transaction.template.billing_end_date', array_get($split, 'template.billing_end_date', \Carbon\Carbon::now()->toDateString()) )))->toDateString() }}
            </div>
            @if ($errors->has('billing_end_date'))
            <span class="help-block text-danger">
                <small><strong>{{ $errors->first('billing_end_date') }}</strong></small>
            </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-2">
        <div class="form-group {{$errors->has('amount') ? 'has-danger':''}}">
            <span class="text-danger">*</span> {{ Form::label('amount', __('Amount')) }}
            {{ Form::number('amount', array_get($split, 'amount'), ['class' => 'form-control calculate', 'step' => 0.01, 'required' => true, 'autocomplete' => 'off']) }}
            @if ($errors->has('amount'))
            <div class="help-block text-danger">
                <small><strong>{{ $errors->first('amount') }}</strong></small>
            </div>
            @endif
        </div>
    </div>
    
    @if($create_pledge !== 'true')
    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('category', __('Payment category')) }}
            {{ Form::select('category', ['check' => 'Check', 'cash' => 'Cash', 'ach' => 'ACH', 'cc' => 'Credit Card', 'cashapp' => 'Cashapp', 'venmo' => 'Venmo', 'paypal' => 'Paypal', 'facebook' => 'Facebook', 'goods' => 'Goods', 'other' => 'Other', 'unknown' => 'Unknown'], array_get($split, 'transaction.paymentOption.category'), ['class' => 'form-control']) }}
        </div>
    </div>
    @endif
    
    <div class="col-sm-2 transaction_pay_date">
        <div class="form-group">
            @if($create_pledge === 'true')
            {{ Form::label('promised_pay_date', __('Promised pay date')) }}
            {{ Form::text('promised_pay_date', displayLocalDateTime(array_get($split, 'template.billing_start_date', \Carbon\Carbon::now()->toDateString()))->toDateString(), ['class' => 'form-control calendar']) }}
            @else
            {{ Form::label('transaction_initiated_at', __('Transaction Date')) }}
            {{ Form::text('transaction_initiated_at', displayLocalDateTime(array_get($split, 'transaction.transaction_initiated_at', \Carbon\Carbon::now()->toDateString()))->toDateString(), ['class' => 'form-control calendar']) }}
            @endif
        </div>
    </div>
    
    <div class="col-sm-3">
        <div class="form-group payment_option {{$errors->has('payment_option_id') ? 'has-danger':''}}">
            {{ Form::label('payment_option_id', __('Payment Option')) }}
            {{ Form::select('payment_option_id', ['-1' => 'Select Option', '0' => 'New Check'], null, ['class' => 'form-control']) }}
            @if ($errors->has('payment_option_id'))
            <div class="help-block text-danger">
                <small><strong>{{ $errors->first('payment_option_id') }}</strong></small>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row recurring-row">
    <div class="col-sm-12">
        <table class="table">
            <tbody>
                <tr>
                    <td>
                        <strong>@lang('Total at the end of recurring payments'):</strong>
                        <span id="js-total-amount" class="badge badge-pill badge-success p-2">$ {{ number_format(array_get($split, 'amount', 0) * array_get($split, 'transaction.template.billing_cycles', array_get($split, 'template.billing_cycles', 1)), 2) }}</span>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="form-group col-sm-4 cc">
        {{ Form::label('card_type', __('Credit Card Type')) }}
        {{ Form::select('card_type', $cc, null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group col-sm-4 cc ach {{$errors->has('first_four') ? 'has-danger':''}}">
        <span class="text-danger">*</span> 
        <label>@lang('Enter first four digits of') <span class="last_four_type">@lang('Check')</span></label>
        {{ Form::text('first_four', null, ['class' => 'form-control', 'maxlength' => 4, 'autocomplete' => 'off']) }}
        @if ($errors->has('first_four'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('first_four') }}</strong></small>
        </span>
        @endif
    </div>
    <div class="form-group col-sm-4 cc check ach {{$errors->has('last_four') ? 'has-danger':''}}">
        <span class="text-danger">*</span> 
        <label>@lang('Enter last four digits of') <span class="last_four_type">@lang('Check')</span></label>

        @if(in_array(array_get($split, 'transaction.paymentOption.category'), ['check']))
        {{ Form::text('last_four', array_get($split, 'transaction.paymentOption.last_four'), ['class' => 'form-control', 'maxlength' => 4, 'autocomplete' => 'off']) }}
        @else
        {{ Form::text('last_four', null, ['class' => 'form-control', 'maxlength' => 4, 'autocomplete' => 'off']) }}
        @endif
        @if ($errors->has('last_four'))
        <span class="help-block text-danger">
            <small><strong>{{ $errors->first('last_four') }}</strong></small>
        </span>
        @endif
    </div>
</div>


@if( $create_pledge !== 'true' )
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('channel', __('Channel')) }}
            {{ Form::select('channel', ['face_to_face' => 'Face to Face', 'mail' => 'Mail', 'ncf' => 'Appreciated Stock Through NCF', 'event' => 'Event', 'other' => 'Other', 'unknown' => 'Unknown', 'ctg_direct' => 'CTG - Direct', 'ctg_embed' => 'CTG - Website Embedded Form', 'ctg_text_link' => 'CTG - Text For Link', 'ctg_text_give' => 'CTG - Text To Give', 'website' => '(deprecated) Website'], 'unknown', ['class' => 'form-control']) }}
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('tax_deductible', __('Tax Deductible')) }}
            <br>
            <label class="c-switch c-switch-label  c-switch-primary">
                <input type="checkbox" name="tax_deductible" class="c-switch-input" value="1" checked>
                <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

            </label>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="col-sm-12">
        {{ Form::label('comment', __('Comment from donor')) }}
        {{ Form::textarea('comment', array_get($split, 'transaction.comment'), ['class' => 'form-control']) }}
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    function getPaymentOptions(id, callback){
        //alert({{old('payment_option_id')}} + '----');
        var id = $('input[name="contact_id"]').val();
        if( id === '' ){
            Swal.fire('Enter a registered contact','','info');
            $('input[name="contact"]').focus();
            return false;
        }
        
        var category = $('select[name="category"]').val();
        $.get("{{ route('contacts.payment.options') }}", {id: id, category: category}).done(function (data) {
            
            $('select[name="payment_option_id"]').empty();
            $('select[name="payment_option_id"]').append('<option selected="selected" value="-1">Select Option</option>');
            data.forEach(function (item, index) {
                $('select[name="payment_option_id"]').append('<option value="' + item.id + '">' + item.option + '</option>');
            });
            
            switch (category) {
                case 'cc':
                    $('select[name="payment_option_id"]').append('<option value="0">New Credit Card</option>');
                break;
                case 'check':
                    $('select[name="payment_option_id"]').append('<option value="0">New Check</option>');
                break;
                case 'ach':
                    $('select[name="payment_option_id"]').append('<option value="0">New ACH</option>');
                break;
                default:
        
                break;
            }
            
            callback();
        }).fail(function (data) {
            console.log(data.responseText);
            Swal.fire("@lang('Oops! Something went wrong.')",'','error');
        });
    }
    
    (function(){
        $('select[name="campaign_id"]').on('change', function(e){
            if($(this).val() == 1) $('select[name="purpose_id"]').attr('disabled', false)
            else{
                $.get({
                    url: '/crm/ajax/campaigns/chart',
                    data: {
                        campaign_id: $(this).val()
                    }
                }).then(result => {
                    $('select[name="purpose_id"]').val(result.id)
                    $('select[name="purpose_id"]').attr('disabled', true)
                })
            }
        });
        
        $('select[name="type"]').on('change', function(e){
            $('input[name="tax_deductible"]').click();
        });
        
        //select payment categories
        $('select[name="category"]').on('change', function (e) {
            var value = $(this).val();
            var selector = '.' + value;
            
            $('.cc').hide();
            $('.first_four_type, .last_four_type').html($(this).find(':selected').text());
            
            if (['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown'].includes(value)) {
                $(selector).fadeIn();
                $('.payment_option').fadeOut();
            }
            else{
                $('.payment_option').fadeIn();
            }
            var id = $('input[name="contact_id"]').val();
            getPaymentOptions(id, function(){
                var back_category = "{{ array_get($split, 'transaction.paymentOption.category') }}";
                var front_category = $('select[name="category"]').val();
                if(back_category !== front_category){
                    $('select[name="payment_option_id"]').val(-1);
                }
                else{
                    $('select[name="payment_option_id"]').val({{ array_get($split, 'transaction.paymentOption.id') }});
                }
            });
            
            $('input[name="first_four"]').val('');
            $('input[name="last_four"]').val('');
        });
        
        //changle between on payment option or create new one
        $('select[name="payment_option_id"]').on('change', function (e) {
            var category = $('select[name="category"]').val();
            var value = $(this).val();
            
            if (value === '0') {
                $('.'+category).fadeIn();
            }
            else{
                $('.'+category).fadeOut();
            }
        });
        
        $('.autocomplete').autocomplete({
            source: function( request, response ) {
                // Fetch data
                $.ajax({
                    url: "{{ route('contacts.autocomplete') }}",
                    type: 'post',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function( data ) {
                        response( data );
                    }
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                $('input[name=contact_id]').val(ui.item.id);
                getPaymentOptions(ui.item.id, function(){
                    $('select[name="category"]').change();
                });
            }
        });
        
        /* show/hide transaction payment date*/
        $('.is_recurring').on('click', function(e){
            var value = $(this).find('input[type="radio"]').val();
            if( value === '1' ){
                $('.recurring-row').show();
                $('.transaction_pay_date').fadeOut();
            }
            else{
                $('.recurring-row').hide();
                $('.transaction_pay_date').fadeIn();
            }
        });
        
        @if( array_get($split, 'transaction.template.is_recurring', array_get($split, 'template.is_recurring')) === 1 )
            $('.is_recurring:last').click();
        @else
            $('.is_recurring:first').click();
        @endif
        
        //trigger the current selection
        $('.cc, .payment_option').hide();
        
        $('.calculate').on('keyup', function(e){
            calculateTotalAmount();
        });

        $('#billing_period').on('change', function(e){
            var value = $(this).val();
            var billing_cycles = $('#billing_cycles').val();
            if(billing_cycles > 1 && value != 'Bi-Weekly'){
                value += 's';
            }
            $('input[name="label"]').val(value);
        }).change();

        $('#billing_start_date').on('change', function (e) {
            var data = {
                'billing_period': $('#billing_period').val(),
                'billing_cycles': $('#billing_cycles').val(),
                'billing_start_date': $('#billing_start_date').val()
            };

            $.post("{{ route('transactions.calendar.calculate.end.date') }}", data).done(function (data) {
                $('#billing_end_date').html(data);
            }).fail(function (data) {
                Swal.fire("@lang('Oops! Something went wrong.')",'','error');
            });
        });
        
    })();
    
    function calculateTotalAmount(){
        var period = parseFloat($('input[name="billing_cycles"]').val()) || 0;
        var amount = parseFloat($('input[name="amount"]').val()) || 0;
        var total = period * amount;
        
        $('#js-total-amount').html('$ '+total.toFixed(2));
    }
    
</script>
@endpush
