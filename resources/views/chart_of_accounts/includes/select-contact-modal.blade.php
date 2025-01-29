<div class="modal fade" id="select-contact-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">

            {{ Form::hidden('url', route('contacts.show', ['id' => ':id:/edit'])) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Search Contacts')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            {{ Form::open(['route' => ['contacts.show', ':id:/edit'], 'method' => 'GET', 'id' => 'form']) }}
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('contact', __('Name')) }}
                    {{ Form::text('contact', null, ['id' => 'autocomplete', 'class' => 'form-control', 'required' => true]) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                <button type="submit" class="btn btn-primary">@lang('Show')</button>
            </div>
            {{ Form::close() }}
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')

<script type="text/javascript">
    $('#autocomplete').autocomplete({
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
            console.log(suggestion);
        }
    });
    
    $('#select-contact-modal').on('shown.coreui.modal', function () {
        $('input[name=contact]').focus()
    });
</script>
@endpush