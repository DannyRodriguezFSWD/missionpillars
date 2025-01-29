<div id="delete-group-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form role="modal">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group {{$errors->has('group_name') ? 'has-danger':''}}">
                                {{ Form::label('group_name', __('Name')) }}
                                {{ Form::text('group_name', null, ['class' => 'form-control', 'required' => true, 'id' => 'group-name-edit']) }}
                                <p class="error text-center alert alert-danger d-none error-name"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            {{ Form::label('chart_of_account', __('Purpose')) }}
                            {{ Form::select('chart_of_account', ['asset' => 'asset', 'liability' => 'liability', 'equity' => 'equity', 'income' => 'income', 'expense' => 'expense'], null, ['placeholder' => 'Select Purpose', 'class'=>'form-control coa', 'id' => 'group-coa-edit']) }}
                            <p class="error text-center alert alert-danger d-none error-chart_of_account"></p>
                        </div>
                    </div>
                </form>
                <div class="deletegroup">
                    Are you sure you want to delete <span class="title"><span> group?
                    <span class="d-none id"></span>
                </div>
            </div>
            <div class="modal-footer">
                {{ From::button('', ['type' => 'button', 'class' => 'btn actionBtn', 'data-dismiss' => 'modal']) }}
                {{ Form::button('Close', ['type' => 'button', 'class' => 'btn btn-primary', 'data-dismiss' => 'modal']) }}
            </div>
        </div>
    </div>
</div>