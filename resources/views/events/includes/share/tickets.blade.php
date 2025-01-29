@if($soldout)
    <div id="tickets-sold-out" class="badge-pill p-4 badge-info text-center">
        <h1 class="text-white">
            <strong>Sold out</strong>
        </h1>
    </div>
@else
    <div class="row">
        <div class="col-sm-6">
{{--            @if (array_get($event, 'version', 1) == 1)--}}
{{--            <h5>{{ array_get($event, 'name') }}</h5>--}}
{{--            @else--}}
{{--            <h5 class="text-uppercase">@lang('Ticket information')</h5>--}}
{{--            @endif--}}
        </div>

        <div class="col-sm-6 text-right">
            <h3 class="text-uppercase">Total: $<span class="total">0.0</span></h3>
        </div>
    </div>


    <table class="table" id="tickets" style="font-size: 1rem !important;">
        <thead>
            <tr>
                <td>@lang('Amount')</td>
                <td>@lang('Ticket type') 
                    @if ($tickets->count() > 1)
                    <span class="text-danger small one-ticket-warning">
                        <i class="fa fa-exclamation-triangle"></i> Please select at least one ticket
                    </span>
                    @endif
                </td>
                <td>@lang('Subtotal')</td>
                <td>&nbsp;</td>
            </tr>
        </thead>
        <body>
            <tr>
                <td>
                    <input name="ticket_amount[]" type="number" class="amount form-control p-1" min="1" value="1" step="1" required=""/>
                    <small id="number_of_tickets_error" class="text-danger"></small>
                </td>
                <td width="100%">
                    <select name="ticket_type[]" class="price form-control">
                        @if ($tickets->count() > 1)
                            <option value="">Select a ticket</option>
                        @endif
                        
                        @foreach($tickets as $option)
                            @if(!array_get($option, 'is_free_ticket', false) && !array_get($option, 'allow_unlimited_tickets', false))
                                <option {{ array_get($option, 'availability', 0) <= 0 ? 'disabled':'' }} data-unlimited="{{ array_get($option, 'allow_unlimited_tickets', false) }}" data-availability="{{ array_get($option, 'availability', 0) }}" data-price="{{ array_get($option, 'price', 0) }}" value="{{ array_get($option, 'id') }}">
                                    {{ array_get($option, 'name') }}
                                    @if(array_get($option, 'price', 0) > 0)
                                        [{{ array_get($option, 'availability', 0) }}] of {{$option->totalNumberOfTickets}} @lang('available')
                                    @endif
                                </option>
                            @elseif(!array_get($option, 'is_free_ticket', false) && array_get($option, 'allow_unlimited_tickets', false))
                                <option data-unlimited="{{ array_get($option, 'allow_unlimited_tickets', false) }}" data-availability="{{ array_get($option, 'availability', 0) }}" data-price="{{ array_get($option, 'price', 0) }}" value="{{ array_get($option, 'id') }}">
                                    {{ array_get($option, 'name') }}
                                    (@lang('unlimited tickets'))
                                </option>
                            @elseif(array_get($option, 'is_free_ticket', false) && !array_get($option, 'allow_unlimited_tickets', false))
                                <option {{ array_get($option, 'availability', 0) <= 0 ? 'disabled':'' }} data-unlimited="{{ array_get($option, 'allow_unlimited_tickets', false) }}" data-availability="{{ array_get($option, 'availability', 0) }}" data-price="{{ array_get($option, 'price', 0) }}" value="{{ array_get($option, 'id') }}">
                                    {{ array_get($option, 'name') }}
                                    [{{ array_get($option, 'availability', 0) }}] of {{$option->totalNumberOfTickets}} @lang('available')
                                </option>
                            @elseif(array_get($option, 'is_free_ticket', false) && array_get($option, 'allow_unlimited_tickets', false))
                                <option data-unlimited="{{ array_get($option, 'allow_unlimited_tickets', false) }}" data-availability="{{ array_get($option, 'availability', 0) }}" data-price="{{ array_get($option, 'price', 0) }}" value="{{ array_get($option, 'id') }}">
                                    {{ array_get($option, 'name') }}
                                    (@lang('unlimited tickets'))
                                </option>
                            @endif

                        @endforeach
                    </select>
                </td>
                <td>
                    <h4>
                        $<span class="subtotal">0.00</span>
                    </h4>
                </td>
                <td>
                    <!-- <button type="button" class="delete-ticket btn btn-link"><span class="fa fa-minus-circle text-danger"></span></button> -->
                </td>
            </tr>
        </tbody>
    </table>
    @if (array_get($event, 'is_paid') === 1)
        <div class="text-right">
            <button type="button" class="btn btn-primary" id="add-ticket">
                <span class="fa fa-plus"></span>
                @lang('Add more')
            </button>

    @endif
            @php $caption = 'Sign Up' @endphp
            @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(),'size' => 'btn','notFlat' => true, 'next_url' => null, null, 'caption' => $caption, 'form' => false])
        </div>
@endif

@push('scripts')
    @include('events.includes.share.tickets_scripts')
@endpush
