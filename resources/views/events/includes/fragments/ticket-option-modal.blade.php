<div class="modal fade" id="ticket-option-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Ticket Option')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('', __('Ticket Name')) }}
                    {{ Form::text('option_ticket_name', null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                    <span class="option_ticket_name text-danger" style="display: none;">@lang('Enter the ticket\'s name')</span>
                </div>

                <div class="form-group offer_as_free">
                    {{ Form::checkbox('is_free_ticket', 1) }} Offer as free ticket
                </div>

                <div class="form-group offer_as_free" id="offer_paid_tickets">
                    {{ Form::label('', __('Ticket Price')) }}
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                        </div>
                        {{ Form::number('option_ticket_price', 1, ['class' => 'form-control', 'autocomplete' => 'off', 'step' => '0.01', 'min' => '1']) }}
                    </div>

                    <span class="option_ticket_price text-danger" style="display: none;">@lang('Enter the ticket\'s price')</span>
                </div>

                <div class="form-group">
                    {{ Form::checkbox('allow_unlimited_tickets', 1) }} Offer unlimited tickets
                </div>

                <div class="form-group" id="offer_limited_tickets">
                    {{ Form::label('', __('Number of available tickets')) }}
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                        {{ Form::number('ticket_availability', 0, ['class' => 'form-control', 'autocomplete' => 'off', 'step' => '1', 'min' => '0']) }}
                    </div>

                    <span class="option_ticket_availability text-danger" style="display: none;">@lang('Number of available tickets')</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="add-ticket" class="btn btn-primary">@lang('Add')</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Cancel')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@push('scripts')
<script>
    (function(){
        $('#ticket-option-modal').on('show.coreui.modal', function(e){
            var is_paid = $('input[name="is_paid"]').prop('checked');
            if(!is_paid){
                $('.offer_as_free').hide();
            }
        }).on('hide.coreui.modal', function(e){
            $('.offer_as_free').show();
        });

        $('input[name="is_free_ticket"]').on('click', function(e){
            var checked = $(this).prop('checked');
            if(checked){
                $('#offer_paid_tickets').hide();
            }
            else{
                $('#offer_paid_tickets').show();
            }
        });

        $('input[name="allow_unlimited_tickets"]').on('click', function(e){
            var checked = $(this).prop('checked');
            if(checked){
                $('#offer_limited_tickets').hide();
            }
            else{
                $('#offer_limited_tickets').show();
            }
        });
    })();
</script>
@endpush