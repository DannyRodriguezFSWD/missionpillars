
@foreach($tasks as $task)
    @php
    $linked_to = null;
    $assigned_to = null;
    @endphp

    @if(!is_null(array_get($task, 'linkedTo')))
        @php $linked_to = array_get($task, 'linkedTo.first_name').' '.array_get($task, 'linkedTo.last_name').' ('.array_get($task, 'linkedTo.email_1').')'; @endphp
    @endif

    @if(!is_null(array_get($task, 'assignedTo')))
        @php $assigned_to = array_get($task, 'assignedTo.first_name').' '.array_get($task, 'assignedTo.last_name').' ('.array_get($task, 'assignedTo.email_1').')'; @endphp
    @endif

@endforeach

@push('scripts')
<script type="text/javascript">
    (function () {
        $('.edit-time').on('change', function (e) {
            var value = $(this).val();
            if (value == '---') {
                $('.time').hide();
            } else {
                $('.time').show();
            }
        }).change();


        $('.modal').on('show.coreui.modal', function (event) {
            $(this).find('.edit-time').change();
        });

        $('.link').autocomplete({
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
                $('input[name="linked_to"]').val(ui.item.id);
            }
        });

        $('.assign').autocomplete({
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
                $('input[name="assigned_to"]').val(ui.item.id);
            }
        });

        $('.assign').keyup(function (e) {
            if (e.keyCode == 8 || e.keyCode == 46) {
                $('input[name="assigned_to"]').val(0);
            }
        });

        $('.link').keyup(function (e) {
            if (e.keyCode == 8 || e.keyCode == 46) {
                $('input[name="linked_to"]').val(0);
            }
        });

        $('.task-form').on('submit', function (e) {
            var value = $(this).find('input[name="assigned_to"]').val();
            if (value == 0 || value == null || value == 'undefined' || value == '') {
                Swal.fire('Tasks should be assigned to a contact','','info');
                $(this).find('input[name="assigned_to_contact"]').focus();
                return false;
            }

            value = $(this).find('input[name="linked_to"]').val();
            if (value == 0 || value == null || value == 'undefined' || value == '') {
                Swal.fire('Tasks should be linked to a contact','','info');
                $(this).find('input[name="link_to_contact"]').focus();
                return false;
            }
        });

    })();
</script>
@endpush
