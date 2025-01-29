{{--
Requires:
integer $relation   The id of the relation

Optional:
string $store_note_redirect a url to redirect to after succesfully storing note
 --}}
 @php
 if (!isset($store_note_redirect)) $store_note_redirect = route('contacts.notes', $relation->id)
 @endphp
<div class="modal fade" id="new-note-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success modal-lg" role="document">
        {{ Form::open(['route' => 'notes.store']) }}
        {{ Form::hidden('relation_id', $relation->id) }}
        {{ Form::hidden('relation_type', get_class($relation)) }}
        {{ Form::hidden('redirect', $store_note_redirect ) }}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Add Note')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Title', 'required' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::textarea('content', null, ['class' => 'form-control', 'placeholder' => 'Start writing']) }}
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </div>
                        {{ Form::text('date', date('Y-m-d'), ['class' => 'form-control datepicker', 'placeholder' => 'Date', 'required' => true]) }}
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


@push('scripts')
    <script type="text/javascript">
      (function () {
          $('#new-note-modal').on('hidden.coreui.modal', function (e) {
              $(this).find('input[name="title"]').val('');
              $(this).find('textarea[name="content"]').val('');
          });
      })();
    </script>
@endpush()
