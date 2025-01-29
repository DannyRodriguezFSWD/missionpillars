<div class="modal fade" id="select-contact-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => ['entries.update', $entry->id], 'method' => 'PUT']) }}
            {{ Form::hidden('uid', Crypt::encrypt($entry->id)) }}
            <div class="modal-header">
                <h4 class="modal-title">@lang('Connect to')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group {{ $errors->has('cid') ? 'form-control has-danger': '' }}">
                    {{ Form::label('contact', __('Name')) }}
                    @if ($errors->has('cid'))
                        <div class="help-block text-danger">
                            <small>
                                <strong>{{ $errors->first('cid') }}</strong>
                            </small>
                        </div>
                        @push('scripts')
                        <script type="text/javascript">
                            (function(){
                                $('#existing-contact').trigger('click');
                            })();
                        </script>
                        @endpush
                    @endif

                    {{ Form::text('contact', null, ['id' => 'autocomplete', 'class' => 'form-control', 'required' => true]) }}
                    {{ Form::hidden('cid', null) }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('Connect')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
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
            console.log('You selected: ' + ui.item.value + ', ' + ui.item.data);
            $('input[name=cid]').val(ui.item.data);
        }
    });
</script>
@endpush