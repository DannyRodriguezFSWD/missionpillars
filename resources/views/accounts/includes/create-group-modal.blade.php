<div id="create-group-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => 'store-group', 'role' => 'form']) }}
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group {{$errors->has('group_name') ? 'has-danger':''}}">
                            {{ Form::label('group_name', __('Name')) }}
                            {{ Form::text('group_name', null, ['class' => 'form-control', 'required' => true, 'id' => 'group-name']) }}
                            <p class="error text-center alert alert-danger d-none error-name"></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        {{ Form::label('chart_of_account', __('Purpose')) }}
                        {{ Form::select('chart_of_account', ['asset' => 'asset', 'liability' => 'liability', 'equity' => 'equity', 'income' => 'income', 'expense' => 'expense'], null, ['placeholder' => 'Select Purpose', 'class'=>'form-control coa']) }}
                        <p class="error text-center alert alert-danger d-none error-chart_of_account"></p>
                    </div>
                </div>
                
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                {{ Form::button('<i class="icons icon-note"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'add-group']) }}
                {{ Form::button('Close', ['type' => 'button', 'class' => 'btn btn-primary', 'data-dismiss' => 'modal']) }}
            </div>
        </div>
    </div>
</div>