@if($split->transaction->status == 'failed')
<span class="badge badge-danger badge-pill p-2">{{ $split->transaction->status }}</span>
@elseif($split->transaction->status == 'pending')
<span class="badge badge-warning badge-pill p-2">{{ $split->transaction->status }}</span>
@elseif($split->transaction->status == 'complete')
<span class="badge badge-success badge-pill p-2">{{ $split->transaction->status }}</span>
@elseif($split->transaction->status == 'refunded')
<span class="badge badge-info badge-pill p-2">{{ $split->transaction->status }}</span>
@else
<span class="badge badge-default badge-pill p-2">{{ $split->transaction->status }}</span>
@endif