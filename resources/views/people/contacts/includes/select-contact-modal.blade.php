<div class="modal fade" id="select-contact-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">

            {{ Form::hidden('url', route('contacts.show', ['id' => ':id:'])) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search Contacts')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route' => ['contacts.show', ':id:'], 'method' => 'GET', 'id' => 'form']) }}
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('contact', __('Name or email')) }}
                    {{ Form::text('contact', null, ['id' => 'autocomplete', 'class' => 'form-control', 'required' => true]) }}
                </div>
            </div>
            <div class="modal-footer">
{{--                <button type="submit" class="btn btn-primary" id="contact-submit" disabled>@lang('Show')</button>--}}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script type="text/javascript" src="{{ asset('js/forms/jquery.autocomplete.min.js') }}"></script>

<script type="text/javascript">
    $('#autocomplete').autocomplete({
        source: function( request, response ) {
            $('#contact-submit').prop('disabled', true);
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
            var action = $('input[name=url]').val().replace(':id:', ui.item.id);
            $('#form').attr('action', action);
            $('#form').submit();
            $('#select-contact-modal').modal('hide');
            $('#contact-submit').prop('disabled', false);
        }
    });

    $('#select-contact-modal').on('show.coreui.modal', function () {
        $('input[name=contact]').val('');
        $('#contact-submit').prop('disabled', true);
    });

    $('#select-contact-modal').on('shown.coreui.modal', function () {
        $('input[name=contact]').focus()
    });

    $('#select-contact-modal').on('hidden.coreui.modal', function () {
        $('input[name=contact]').val('');
        $('#contact-submit').prop('disabled', true);
    });

    $('#contact-submit').click(function() {
        $('#select-contact-modal').modal('hide');
    });

    $('#form').on("keyup keypress", function(e) {
        var code = e.keyCode || e.which;
        if (code  == 13) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush