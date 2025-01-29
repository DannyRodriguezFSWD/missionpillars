<div class="modal fade" id="delete-email-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Delete Email Stats')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you sure you want to delete list:') <strong>{{ array_get($email, 'subject') }}</strong>?</p>
                <small>@lang("This action can't be undone")</small>
            </div>
            <div class="modal-footer">
                <button id="button-delete" type="button" class="btn btn-warning" data-dismiss="modal">@lang('Delete Email Stats')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

{{ Form::open(['route' => ['emails.destroy', array_get($email, 'id')], 'id' => 'form', 'method' => 'DELETE']) }}
{{ Form::hidden('uid', Crypt::encrypt(array_get($email, 'id')) ) }}
{{ Form::close() }}

@push('scripts')
<script type="text/javascript">
    $('#button-delete').on('click', function (e) {
        $('#form').submit();
    });
</script>
@endpush