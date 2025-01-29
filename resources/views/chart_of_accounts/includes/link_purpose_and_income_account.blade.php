@if($link_purposes_and_accounts)
        <div class="card-body">
            <h4>You can link this purpose to a specific income account</h4>
            <div class="form-group">
                {{ Form::label('account_input', 'Select income account') }}
                {{ Form::text('account_input', $account_name, ['class' => 'form-control', 'id' => 'account_input']) }}
                {{ Form::hidden('account_id', array_get($account, 'id')) }}
            </div>
        </div>
        <div class="card-body">
            <h4>You can link this purpose to a specific fund</h4>
            <div class="form-group">
                {{ Form::label('fund_input', 'Select fund') }}
                {{ Form::text('fund_input', $fund_name, ['class' => 'form-control', 'id' => 'fund_input']) }}
                {{ Form::hidden('fund_id', array_get($account, 'id')) }}
            </div>
        </div>
        @push('scripts')
            <script>
                (function(){
                    $('#account_input').autocomplete({
                        source: function( request, response ) {
                            // Fetch data
                            $.ajax({
                                url: '/accounting/ajax/accounts/autocomplete',
                                type: 'post',
                                dataType: "json",
                                data: {
                                    search: request.term,
                                    account_type: 'income'
                                },
                                success: function( data ) {
                                    response( data );
                                }
                            });
                        },
                        minLength: 0,
                        select: function( event, ui ) {
                            $('input[name=account_id]').val( ui.item.id );
                        },
                    }).on('keydown', function(e){
                        if(e.which != 13) {
                            $('input[name=account_id]').val('null');
                        }
                    }).focus(function(){
                        $(this).data("uiAutocomplete").search($(this).val())
                    });

                    $('#fund_input').autocomplete({
                        source: function( request, response ) {
                            $.ajax({
                                url: '/accounting/ajax/funds/autocomplete',
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
                        minLength: 0,
                        select: function( event, ui ) {
                            $('input[name=fund_id]').val( ui.item.id );
                        },
                    }).on('keydown', function(e){
                        if(e.which != 13) {
                            $('input[name=fund_id]').val('null');
                        }
                    }).focus(function(){
                        $(this).data("uiAutocomplete").search($(this).val())
                    });
                })();
            </script>
        @endpush
        @endif