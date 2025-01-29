<script type="text/javascript">
    @if (session('error'))
        Swal.fire('', '{{ session('error') }}', 'error');
    @endif
    
    (function () {
        function calc(sender) {
            $('#number_of_tickets_error').html("");
            var row = sender.parents('tr');
            var amount = row.find('.amount:first').val();
            var price = row.find('.price:first').find(':selected').data('price');
            var availability = row.find('.price:first').find(':selected').data('availability');
            var unlimited = row.find('.price:first').find(':selected').data('unlimited');

            if(amount > availability && !unlimited){
                $('#number_of_tickets_error').html("You can only select "+availability+" available tickets");
                amount = availability;
                row.find('.amount:first').val(amount);
            }

            if (price) {
                var subtotal = amount * price;
                row.find('.subtotal:first').html(subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            } else {
                var subtotal = 0;
                row.find('.subtotal:first').html(subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
            
            var total = 0;
            
            $('.subtotal').each(function (index) {
                total += parseFloat($(this).html().replace(',', ''));
            });
            
            if (total > 0) {
                $('.one-ticket-warning').hide();
            } else {
                $('.one-ticket-warning').show();
            }
            
            $('input[name="total"]').val(total);
            $('span.total').html(total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
        }

        $('#tickets').on('change', '.amount', function (e) {
            calc($(this));
        });

        $('#tickets').on('keyup', '.amount', function (e) {
            calc($(this));

            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    // Allow: Ctrl+A, Command+A
                            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                            // Allow: home, end, left, right, down, up
                                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                        // let it happen, don't do anything
                        return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }

                    var tr = $(this).parents('tr');
                    tr.find('.delete-ticket:first').show();
                });

        $('#tickets').on('change', '.price', function (e) {
            calc($(this));
        });

        $('#tickets').on('click', '.delete-ticket', function (e) {
            var tr = $(this).parents('tr');
            tr.remove();
            calc($(this));
        });

        $('#tickets .price').change();
        $('[data-toggle="tooltip"]').tooltip();

        @if (array_get($event, 'is_paid') === 1)
            var row = $('#tickets').find('tbody tr:first');

            $('#add-ticket').on('click', function(e){
                var select = '<select name="ticket_type[]" class="price form-control">' + row.find('.price:first').html() + '</select>';
                var tr = '<tr>';
                tr += '<td><input name="ticket_amount[]" type="number" class="amount form-control p-1" min="1" step="1" value="1"></td>';
                tr += '<td>' + select + '</td>';
                tr += '<td><h4>$<span class="subtotal">0.00</span></h4></td>';
                tr += '<td><button data-toggle="tooltip" data-placement="bottom" data-original-title="Remove ticket(s)" type="button" class="delete-ticket btn btn-link"><span class="fa fa-minus-circle text-danger"></span></button></td>';
                tr += '<tr>';

                var last = $('.amount:last').val();
                if (last !== '') {
                    $('#tickets').find('tbody').append(tr);
                    $('#tickets .price').change();
                }
            });
        @endif
    })();
</script>