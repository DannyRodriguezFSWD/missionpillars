@php
    $completedonly = true;
@endphp
@if( auth()->user()->can('transaction-view') )
    <div class="row transactions-pane">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-success text-light text-center border-light">
                <div class="card-body pt-1 pb-1">
                    <div class="small text-uppercase font-weight-bold">
                        @lang('Latest Transaction')
                    </div>
                    @if (!$last_gift)
                        <div class="text-value-lg"> <em>None</em> </div>
                        <div> &nbsp; </div>
                    @else
                        <div class="text-value-lg">
                            ${{ number_format($last_gift->splits()->sum('amount'), 2) }}
                        </div>
                        <div>
                            {{ displayLocalDateTime(array_get($last_gift, 'transaction_initiated_at'))->format('D, M j, Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-success text-light text-center border-light">
                <div class="card-body pt-1 pb-1">
                    <div class="small text-uppercase font-weight-bold">
                        @lang('this year')
                    </div>
                    <div class="text-value-lg">
                        ${{ number_format($total_amount_this_year, 2) }}
                    </div>
                    <div> &nbsp; </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-success text-light text-center border-light">
                <div class="card-body pt-1 pb-1">
                    <div class="small text-uppercase font-weight-bold">
                        @lang('last year')
                    </div>
                    <div class="text-value-lg">
                        ${{ number_format($total_amount_last_year, 2) }}
                    </div>
                    <div> &nbsp; </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-success text-light text-center border-light">
                <div class="card-body pt-1 pb-1">
                    <div class="small text-uppercase font-weight-bold">
                        Total amount
                    </div>
                    <div class="text-value-lg">
                        ${{ number_format($completedtransactions->sum('amount')) }}
                    </div>
                    <div> &nbsp; </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
    </div>
    
    <style>
    .transactions-pane div.card {
        white-space: nowrap;
        min-width: 18ch;
    }
    </style>
@endif
