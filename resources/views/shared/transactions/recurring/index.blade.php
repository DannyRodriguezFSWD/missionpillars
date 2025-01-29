
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>
                @php $params = http_build_query(['sort' => 'amount', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Amount')
                    @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'amount' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'amount' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'type', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Type')
                    @if( isset($order) && $order === 'asc' && $sort === 'type' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($order) && $order === 'desc' && $sort === 'type' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'for', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Purpose')
                    @if( isset($order) && $order === 'asc' && $sort === 'for' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'campaign', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Fundraiser')
                    @if( isset($order) && $order === 'asc' && $sort === 'campaign' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($order) && $order === 'desc' && $sort === 'campaign' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>

            <th>
                @php $params = http_build_query(['sort' => 'cycle', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Billing Cycles')
                    @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'cycle' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'cycle' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'frequency', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Billing Frecuency')
                    @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'frequency' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'frequency' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'successes', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Successes')
                    @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'successes' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'successes' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>
                @php $params = http_build_query(['sort' => 'remaining', 'order' => $nextOrder]); @endphp
                <a href="{{ url()->current().'?'.$params }}">
                    @lang('Remaining')
                    @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'remaining' )
                        <i class="fa fa-caret-down"></i>
                    @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'remaining' )
                        <i class="fa fa-caret-up"></i>
                    @endif
                </a>
            </th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        @foreach($splits as $split)
            <tr class="clickable-row" data-href="{{ route('recurring.show', ['id' => $split->id]) }}">
                <td>
                    <span class="badge badge-pill badge-primary p-2">$ {{ $split->amount }}</span>
                </td>
                <td>
                    <small>{{ $split->type }}</small>
                </td>
                <td>
                    <small>{{ $split->purpose ? $split->purpose->name : '' }}</small>
                </td>
                <td>
                    <small>{{ $split->campaign ? $split->campaign->name : 'None' }}</small>
                </td>
                <td>
                    <small>{{ $split->template ? $split->template->billing_cycles : '' }}</small>
                </td>
                <td>
                    <small>{{ $split->template ? $split->template->billing_frequency.' '.$split->template->billing_period. ($split->template->billing_frequency > 1 ? 's':'') : '' }}</small>
                </td>

                <td>
                    <small>{{ $split->template && !is_null($split->template->successes) ? $split->template->successes : '0' }}</small>
                </td>

                <td>
                    <small>{{ $split->template && !is_null($split->template->successes) ? $split->template->billing_cycles - $split->template->successes : $split->template->billing_cycles }}</small>
                </td>

                <td class="text-right">
                    <span class="icon icon-arrow-right btn"></span>
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="card-body">
    @if(count($splits) > 0)
    @if( isset($search) && $search === 'range' )
    @if( isset($sort) )
    {{ $splits->appends(['sort' => $sort, 'order' => $order, 'min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
    @else
    {{ $splits->appends(['min' => app('request')->input('min'), 'max' => app('request')->input('max')])->links() }}
    @endif
    @elseif( isset($search) && $search === 'contact' )
    @if( isset($sort) )
    {{ $splits->appends(['sort' => $sort, 'order' => $order, 'keyword' => app('request')->input('keyword')])->links() }}
    @else
    {{ $splits->appends(['keyword' => app('request')->input('keyword')])->links() }}
    @endif
    @elseif( isset($search) && $search === 'status' )
    @if( isset($sort) )
    {{ $splits->appends(['sort' => $sort, 'order' => $order, 'status' => app('request')->input('status')])->links() }}
    @else
    {{ $splits->appends(['status' => app('request')->input('status')])->links() }}
    @endif
    @elseif( isset($sort) )
    {{ $splits->appends(['sort' => $sort, 'order' => $order])->links() }}
    @else
    {{ $splits->links() }}
    @endif
    @endif

</div>
