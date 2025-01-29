@push('scripts')
<script type="text/javascript">
    (function(){
        @if( !is_null($start_time) )
            $('#start_time').val('{{ $start_time }}');
            $('#end_time').val('{{ $end_time }}');
        @endif

        $('select[name="campaign_id"]').on('change', function(e){
            var id = $(this).val();
            if( id <= 1 ){
                $('select[name="purpose_id"]').val(1).prop('disabled', false);
                return;
            }
            $.get("{{ route('ajax.get.chartfromcampaign') }}", {campaign_id: id}).done(function(data){
                if( data !== false ){
                    $('select[name="purpose_id"]').val(data.id).prop('disabled', true);
                }
            }).fail(function(data){

            });
        });

        $('#form').on('submit', function(e){

            if( $('input[name="is_paid"]').prop('checked') && $('select[name="purpose_id"]').val() == "1" ){
                Swal.fire("Select Purpose or Fundraiser",'','info');
                $('select[name="purpose_id"]').focus();
                return false;
            }

            $('select[name="purpose_id"]').prop('disabled', false);
        });

        $('#paid_event_settings').hide();
        $('input[name=is_all_day]').on('click', function (e) {
            var checked = $(this).prop('checked');

            if (checked === false) {
                $('#full-day').hide();
                $('#not-full-day').show();
            } else {
                $('#not-full-day').hide();
                $('#full-day').show();
            }
        });

        $('input[name=event_repeats]').on('click', function (e) {
            var checked = $(this).prop('checked');

            if (checked === true) {
                $('#event-repeats').show();
            } else {
                $('#event-repeats').hide();
            }
        });

        $('select[name=repeat_cycle]').on('change', function (e) {
            var value = $(this).val().toLowerCase();
            $('input[name="repeat_every"]').val(1);
            switch (value) {
                case 'weekly':
                    $('#text').html('Weeks');
                    break;
                case 'monthly':
                    $('#text').html('Months');
                    break;
                case 'yearly':
                    $('#text').html('Years');
                    break;
                default:
                    $('#text').html('Days');
                    break;
            }
        });
        
        $('select[name=calendar_id]').on('change', function (e) {
            var color = $(this).find(':selected').data('background');

            $('#calendar-color').css({
                background: color
            });
        });

        $('.autocomplete').autocomplete({
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
                $('input[name=contact_id]').val(ui.item.id);
            }
        });

        $('input[name="is_paid"]').on('click', function (e) {
            var checked = $(this).prop('checked');
            if (checked) {
                $('#paid_event_settings').fadeIn();
            } else {
                $('#paid_event_settings').fadeOut();
            }
        });

        $('#add-ticket').on('click', function(e){
            var name = $('input[name="option_ticket_name"]').val();
            var price = $('input[name="option_ticket_price"]').val();
            var ticket_availability = $('input[name="ticket_availability"]').val();
            var is_free_ticket = $('input[name="is_free_ticket"]').prop('checked');
            var allow_unlimited_tickets = $('input[name="allow_unlimited_tickets"]').prop('checked');
            var is_paid = $('input[name="is_paid"]').prop('checked');

            if( name.trim() === '' ){
                $('span.option_ticket_name').show();
                return false;
            }

            if( price.trim() === '' ){
                $('span.option_ticket_price').show();
                return false;
            }

            var tr = '<tr>';
            tr += '<td><div class="input-group"> <div class="input-group-prepend"> <span class="input-group-text d-sm-down-none"><i class="fa fa-ticket"></i></span> </div>';
            tr += '<input type="text" name="ticket_name[]" value="'+name+'" class="ticket-option form-control">';
            tr += '<input type="hidden" name="ticket_record[]" value="0"/>';
            var is_free_ticket_value = (is_free_ticket || !is_paid) ? 1:0;
            var allow_unlimited_tickets_value = allow_unlimited_tickets ? 1 : 0;
            tr += '<input type="hidden" name="is_free_ticket[]" value="'+is_free_ticket_value+'"/>';
            tr += '<input type="hidden" name="allow_unlimited_tickets[]" value="'+allow_unlimited_tickets_value+'"/>';
            tr += '</div></td>';
            if(is_free_ticket || !is_paid){
                tr += '<td><div class="badge-pill badge-info p-2 text-white text-center">Free<input type="hidden" name="ticket_price[]" value="0" class="ticket-option"></div></td>';
            }
            else{
                tr += '<td><div class="input-group"> <div class="input-group-prepend"> <span class="input-group-text d-sm-down-none"> <i class="fa fa-dollar" aria-hidden="true"></i> </span> </div>' +
                    '<input type="number" step="0.01" name="ticket_price[]" value="'+price+'" class="ticket-option form-control">' +
                    '</div></td>';
            }

            if(allow_unlimited_tickets){
                tr += '<td><div class="badge-pill badge-warning p-2 text-white text-center">Unlimited<input type="hidden" name="ticket_availability[]" value="0" class="ticket-option"></div></td>';
            }
            else{
                tr += '<td><div class="input-group"> <div class="input-group-prepend"> <span class="input-group-text d-sm-down-none"> <i class="fa fa-hashtag" aria-hidden="true"></i> </span> </div>' +
                    '<input type="number" name="ticket_availability[]" value="'+ticket_availability+'" class="ticket-option form-control">' +
                    '</div></td>';
            }

            tr += '<td class="text-right"><button type="button" class="delete-ticket-option btn btn-link px-0"><span class="fa fa-minus-circle text-danger"></span></button></td>';
            tr += '</tr>';
            $('#ticket-options').find('tbody').append(tr);

            $('input[name="option_ticket_name"]').val('');
            $('input[name="option_ticket_price"]').val(1);

            $('#ticket-option-modal').modal('hide');
        });


        $('#ticket-options').on('click', '.delete-ticket-option', function(e){
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this ticket option?',
                type: 'question',
                showCancelButton: true,
            }).then(res => {
                if (res.value){
                    var tr = $(this).parents('tr');
                    tr.remove();
                }
            })
        });

        $('select[name=calendar_id]').trigger('change');
        $('select[name="repeat_cycle"]').trigger('change');

        @if( array_get($event, 'is_all_day') == 1 )
            $('input[name=is_all_day]').trigger('click');
        @endif

        @if( array_get($event, 'repeat') === 1 )
            $('input[name="event_repeats"]').trigger('click');
            $('.schedule').hide();
            $('#event-repeats').hide();
        @endif

        @if( array_get($event, 'remind_manager') === 1 )
            $('input[name="remind_manager"]').trigger('click');
        @endif
        
        @if( !is_null(array_get($event, 'repeat_ends')) )
            $('#{!! $event->repeat_ends !!}').trigger('click');
        @endif

        @if(is_null($event))
            $('#paid_event_settings').hide();
        @elseif( array_get($event, 'is_paid') === 1 )
            $('#paid_event_settings').fadeIn();
        @endif

        @if(array_get($event, 'allow_reserve_tickets') == 1)
            $('#allow_reserve_tickets_settings').show();
        @endif

        $('#btn-schedule-yes').on('click', function(e){
            $('input[name="rescheduled"]').val(1);
            $('.schedule').fadeIn();
            $('#event-repeats').fadeIn();
            $('.alert-schedule').hide();
        });

        $('input[name="allow_reserve_tickets"]').on('click', function(e){
            var checked = $(this).prop('checked');
            if(checked){
                $('#is_paid_event, #allow_reserve_tickets_settings, #whose_ticket').fadeIn();
            }
            else{
                //if disable "reserve tickets" then disable event is_paid too
                var is_paid = $('#is_paid_event').find('input[name="is_paid"]').prop('checked');
                var whose_ticket = $('#whose_ticket').find('input[name="ask_whose_ticket"]').prop('checked');
                if(is_paid){
                    $('#is_paid_event').find('input[name="is_paid"]').click();
                }
                if(whose_ticket){
                    $('#whose_ticket').find('input[name="ask_whose_ticket"]').click();
                }
                $('#is_paid_event, #allow_reserve_tickets_settings, #whose_ticket').fadeOut();
            }
        });

        $('select[name="campaign_id"]').on('change', function(e){
            var id = $(this).val();
            if( id <= 1 ){
                $('select[name="purpose_id"]').val(1).prop('disabled', false);
                return;
            }
            $.get("{{ route('ajax.get.chartfromcampaign') }}", {campaign_id: id}).done(function(data){
                if( data !== false ){
                    $('select[name="purpose_id"]').val(data.id).prop('disabled', true);
                }
            }).fail(function(data){

            });
        });

        @if( array_get($event, 'allow_reserve_tickets') )
            $('#is_paid_event').fadeIn();
            $('#whose_ticket').fadeIn();
        @endif
    })();
</script>
@endpush
