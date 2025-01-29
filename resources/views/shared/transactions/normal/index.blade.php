
           
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
                                @if( isset($order) && $order === 'asc' && $sort === 'for' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'for' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $params = http_build_query(['sort' => 'contact', 'order' => $nextOrder]); @endphp
                            <a href="{{ url()->current().'?'.$params }}">
                                @lang('Contact')
                                @if( isset($order) && $order === 'asc' && $sort === 'contact' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($order) && $order === 'desc' && $sort === 'contact' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $params = http_build_query(['sort' => 'date', 'order' => $nextOrder]); @endphp
                            <a href="{{ url()->current().'?'.$params }}">
                                @lang('Time')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'date' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'date' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $params = http_build_query(['sort' => 'status', 'order' => $nextOrder]); @endphp
                            <a href="{{ url()->current().'?'.$params }}">
                                @lang('status')
                                @if( isset($nextOrder) && $nextOrder === 'asc' && $sort === 'status' )
                                    <i class="fa fa-caret-down"></i>
                                @elseif( isset($nextOrder) && $nextOrder === 'desc' && $sort === 'status' )
                                    <i class="fa fa-caret-up"></i>
                                @endif
                            </a>
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($splits as $split)
                        <tr class="clickable-row" data-href="{{ route('transactions.show', ['id' => $split->id]) }}">
                            <td>
                                <span class="badge badge-pill badge-primary p-2">$ {{ number_format(array_get($split, 'amount', 0), 2) }}</span>
                            </td>
                            <td>
                                <small>{{ $split->givingFor() }}</small>
                            </td>
                            <td>
                                <small>{{ array_get($split, 'campaign.name', 'None') }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ array_get($split, 'transaction.contact.first_name') }}
                                    {{ array_get($split, 'transaction.contact.last_name') }}
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ displayLocalDateTime(array_get($split, 'transaction.transaction_initiated_at')) }}
                                </small>
                            </td>
                            <td class="text-center">
                                @include('transactions.includes.status-color-indicator')
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
            