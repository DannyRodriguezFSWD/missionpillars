@if($group->tenant_id)
<a href="#" class="dropdown-item delete-group">
    <i class="fa fa-trash"></i>&nbsp;@lang('Delete Group')
</a>

{{ Form::model($group, ['route' => ['groups.destroy', $group->id], 'method' => 'delete', 'id'=>'delete-form-'.$group->id]) }}
{{ Form::hidden('uid',  Crypt::encrypt($group->id)) }}
{{ Form::close() }}
@endif

@push('scripts')
<script>
    $('.delete-group').on('click', function (e) {
        $(function () {
            Swal.fire({
                title: 'Are you sure you want to delete this group?',
                type: 'question',
                showCancelButton: true
            }).then(res => {
                if (res.value){
                    Swal.showLoading();
                    $('#delete-form-{{ $group->id }}').submit();
                }
            });
        });
    });
</script>
@endpush