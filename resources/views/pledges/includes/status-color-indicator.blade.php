@if(array_get($template, 'status') == 'complete')
<span class="badge badge-success badge-pill p-2">{{ array_get($template, 'status') }}</span>
@elseif(array_get($template, 'status') == 'active')
<span class="badge badge-info badge-pill p-2">{{ array_get($template, 'status') }}</span>
@elseif(array_get($template, 'status') == 'unaccomplished')
<span class="badge badge-danger badge-pill p-2">{{ array_get($template, 'status') }}</span>
@else
<span class="badge badge-default badge-pill p-2">{{ array_get($template, 'status') }}</span>
@endif