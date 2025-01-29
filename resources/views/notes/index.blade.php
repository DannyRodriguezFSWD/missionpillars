<div class="row">
    <div class="col-md-12 text-right">
        @include('notes.includes.create-button')
    </div>
</div>
<p>&nbsp;</p>

@foreach($notes as $note)
    <div class="row">
        <div class="col-sm-12">
            <div id="notes">
                <div class="card card-accent-warning p-4">
                    <div class="card-body">
                        <div class="btn-group float-right">
                            <button type="button" class="btn btn-transparent p-0 mr-3" data-toggle="modal" data-target="#edit-note-modal-{{$note->id}}">
                                <i class="fa fa-edit text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-transparent p-0" data-toggle="modal" data-target="#delete-note-modal-{{$note->id}}">
                                <i class="fa fa-trash-o text-danger"></i>
                            </button>
                        </div>
                        <h5 class="mb-1">
                            @lang('Note added by')
                            <small>
                                {{ array_get($note, 'user.contact.first_name') }}
                                {{ array_get($note, 'user.contact.last_name') }}
                            </small>
                        </h5>
                        <h6>@lang('At'): {{ date('m/d/Y', strtotime(array_get($note, 'date'))) }}</h6>
                        <h6>{{ array_get($note, 'title') }}</h6>
                        <p>{{ array_get($note, 'content') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="edit-note-modal-{{$note->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            {{ Form::model($note, ['route' => ['notes.update', $note->id], 'method' => 'put']) }}
            {{ Form::hidden('uid', Crypt::encrypt(array_get($note, 'id'))) }}
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('Edit Note')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Title', 'required']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::textarea('content', null, ['class' => 'form-control', 'placeholder' => 'Start writing']) }}
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            </div>
                            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date', 'required']) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">@lang('Save')</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div class="modal fade" id="delete-note-modal-{{$note->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-warning modal-lg" role="document">
            {{ Form::open(['route' => ['notes.destroy', array_get($note, 'id')], 'method' => 'DELETE']) }}
            {{ Form::hidden('uid', Crypt::encrypt(array_get($note, 'id'))) }}
            {{ Form::hidden('relation_id', $contact->id) }}
            {{ Form::hidden('relation_type', get_class($contact)) }}
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('Delete Note')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you sure you want to delete') {{ array_get($note, 'title') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn btn-warning">@lang('Yes')</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endforeach

<div class="row">
    <div class="col-sm-12">{{ $notes->links() }}</div>
</div>


@include('notes.includes.create-modal')

@push('scripts')
@endpush()
