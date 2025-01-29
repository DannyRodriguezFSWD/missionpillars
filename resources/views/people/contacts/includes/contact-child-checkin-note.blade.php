<div class="card">
    <div class="card-body">
        <h5 class="card-title">Child Checkin Note</h5>
        <p class="card-text" style="min-width: 30ch">
            <p class="mb-0">
                {{ $contact->child_checkin_note ?:'' }}
            </p>
        </p>
    </div>
    <div class="card-footer text-center">
        @can('update',$contact)
            <button data-toggle="modal" data-target="#edit_child_checkin_note" class="btn btn-primary"><i class="fa fa-user-plus"></i> Edit Child Checkin Note</button>
        @endcan
    </div>
</div>

@can('update',$contact)
    <div class="modal fade" id="edit_child_checkin_note">
        <div class="modal-dialog modal-success modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>@lang('Edit Child Checkin Note')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                {{ Form::model($contact, ['route' => ['contacts.update.child-checkin-note', array_get($contact, 'id')], 'method' => 'PUT']) }}
                <div class="modal-body">
                    {{ Form::hidden('uid', Crypt::encrypt(array_get($contact, 'id'))) }}
                    <div class="row">
                        <div class="col-md-12">
                            {{ Form::textarea('child_checkin_note', array_get($contact, 'child_checkin_note'), ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i
                                class="icons icon-note"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endcan