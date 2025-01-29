<div id="create-account-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => 'store-account', 'role' => 'form']) }}
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('account_name') ? 'has-danger':''}}">
                            <strong>{{ Form::label('account_name', __('Name')) }}</strong>
                            {{ Form::text('account_name', null, ['class' => 'form-control', 'required' => true, 'id' => 'account-name']) }}
                            <p class="error-account text-center alert alert-danger d-none error-name"></p>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <strong>{{ Form::label('account-group') }}</strong>
                            {{ Form::select('account-group', [], null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <strong>{{ Form::label('account_number', __('Number')) }}</strong>
                            {{ Form::text('account_number', null, ['class' => 'form-control', 'required' => true, 'id' => 'account-number']) }}
                            <p class="error-account text-center alert alert-danger d-none error-number"></p>
                        </div>
                    </div>
                    <div class="col-sm-6 account-type">
                        <div class="form-group">
                            <strong>{{ Form::label('account_type', __('Type')) }}</strong>
                            {{ Form::select('account_type', ['' => 'None', 'register' => 'Use as a register', 'accounts_receivable' => 'Accounts Receivable'], '', ['class' => 'form-control']) }}
                        </div>  
                    </div>
                    <div class="col-sm-6 account-funds">
                        <div class="form-group">
                            <strong>{{ Form::label('fund', __('Fund')) }}</strong>
                            {{ Form::select('fund', ['1'=>'Test Fund 1', '2'=>'Test Fund 2', '3'=>'Test Fund 3'], '1', ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-sm-6 account-activity">
                        <div class="form-group">
                            <strong>{{ Form::label('activity', __('Activity')) }}</strong>
                            {{ Form::select('activity', ['cash' => 'Cash', 'operating' => 'Operating', 'investing' => 'Investing', 'financing' => 'Financing'], 'cash', ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="row">
                                <label class="ml-3"><strong>Status</strong></label>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>{{ Form::radio('status', '1', true) }} Enable</label>
                                </div>
                                <div class="col-sm-6">
                                    <label>{{ Form::radio('status', '0', false) }} Disable</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12">
                                    <strong>Sub-Account</strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label><input type="checkbox" value="" id="sub-account-check">Make this account a sub-account</label>
                                </div>    
                            </div>
                            <div class="row d-none sub-account-row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('sub-account', __('Sub Account')) }}
                                        {{ Form::text('sub-account', '', ['class'=>'sub_account form-control', 'id' => 'sub-account']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                {{ Form::button('<i class="icons icon-note"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'add-account']) }}
                {{ Form::button('Close', ['type' => 'button', 'class' => 'btn btn-primary', 'data-dismiss' => 'modal']) }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    (function(){
        $('#sub-account-check').change(function() {
            $('.sub-account-row').toggleClass('d-none');
        });
    })();
</script>
@endpush