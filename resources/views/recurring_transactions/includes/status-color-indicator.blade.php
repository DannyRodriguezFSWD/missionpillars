<!--
@if(isset($item))
    @if( is_null(array_get($item, 'status')) || array_get($item, 'status') == 'active')
    <span class="badge badge-success badge-pill p-2">{{ array_get($item, 'status', 'active') }}</span>
    @endif
@else
    @if( is_null(array_get($template, 'status')) || array_get($template, 'status') == 'active')
    <span class="badge badge-success badge-pill p-2">{{ array_get($template, 'status', 'active') }}</span>
    @endif
@endif
-->

@if(isset($item))
    @if( (is_null(array_get($item, 'status')) || array_get($item, 'status') == 'active') && array_get($item, 'billing_cycles') != array_get($item, 'successes') && is_null(array_get($item, 'subscription_suspended')) && is_null(array_get($item, 'subscription_terminated')))
        <span class="badge badge-success badge-pill p-2">@lang('Active')</span>
    @elseif(array_get($item, 'billing_cycles') && array_get($item, 'billing_cycles') == array_get($item, 'successes'))
        <span class="badge badge-primary badge-pill p-2">@lang('Complete')</span>
    @elseif(!is_null(array_get($item, 'subscription_suspended')) && is_null(array_get($item, 'subscription_terminated')))
        <span class="badge badge-warning badge-pill p-2">@lang('Paused')</span>
    @elseif(!is_null(array_get($item, 'subscription_terminated')))
        <span class="badge badge-danger badge-pill p-2">@lang('Canceled')</span>
    @else
        <span class="badge badge-default badge-pill p-2">{{ ucfirst(array_get($item, 'status')) }}</span>
    @endif
@else
    @if( (is_null(array_get($template, 'status')) || array_get($template, 'status') == 'active') && array_get($template, 'billing_cycles') != array_get($template, 'successes') && is_null(array_get($template, 'subscription_suspended')) && is_null(array_get($template, 'subscription_terminated')))
        <span class="badge badge-success badge-pill p-2">@lang('Active')</span>
    @elseif(array_get($template, 'billing_cycles') && array_get($template, 'billing_cycles') == array_get($template, 'successes'))
        <span class="badge badge-primary badge-pill p-2">@lang('Complete')</span>
    @elseif(!is_null(array_get($template, 'subscription_suspended')) && is_null(array_get($template, 'subscription_terminated')))
        <span class="badge badge-warning badge-pill p-2">@lang('Paused')</span>
    @elseif(!is_null(array_get($template, 'subscription_terminated')))
        <span class="badge badge-danger badge-pill p-2">@lang('Canceled')</span>
    @else
        <span class="badge badge-default badge-pill p-2">{{ ucfirst(array_get($item, 'status')) }}</span>
    @endif
@endif
