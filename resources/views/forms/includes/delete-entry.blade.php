@if($entry->tenant_id)
{{ Form::model($entry, ['route' => ['entries.destroy', $entry->id], 'method' => 'delete', 'id'=>'delete-form-'.$entry->id]) }}
<button type="button" class="btn btn-link p-0 text-danger delete-entry" data-name="{{ isset($e->first_name) ? $e->first_name : '' }} {{ isset($e->last_name) ? $e->last_name : '' }}" data-form="#delete-form-{{$entry->id}}" data-toggle="modal" data-target="#delete-entry-modal">
    <span class="fa fa-trash"></span>
</button>
{{ Form::hidden('uid',  Crypt::encrypt($entry->id)) }}
{{ Form::close() }}
@endif

@push('scripts')
<script type="text/javascript">

    $(function () {
        $('.delete-entry').on('click', function (e) {
            currentForm = $(this).data('form');
            var msg = $('#delete-entry-modal').find('p').data('msg').replace(':entry:', $(this).data('name'));
            $('#delete-entry-modal').find('p').html(msg);
        });

        $('#button-delete-entry').on('click', function (e) {
            $(currentForm).submit();
        });
    });

</script>
@endpush