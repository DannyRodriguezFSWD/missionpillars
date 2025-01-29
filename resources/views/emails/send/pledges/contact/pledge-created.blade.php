<h4>Dear {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</h4>
<p>
    Thank you for your{{ array_get($template, 'is_recurring') ? ' recurring ':' ' }}pledge of ${{ array_get($split, 'amount') }} to {{ array_get($campaign, 'id', 1) > 1 ? array_get($campaign, 'name') : array_get($chart, 'name') }}, with a promised pay date of {{ humanReadableDate(array_get($pledge, 'template.billing_start_date')) }}.
</p>
<p>
    @if( array_get($split, 'id') === array_get($pledge, 'id') )
    The remaining balance is ${{ array_get($pledge, 'amount', 0) }}
    @else
    The remaining balance is ${{ array_get($pledge, 'amount', 0) - array_get($split, 'amount', 0) }}
    @endif
</p>
@if( !is_null( array_get($campaign, 'getAltIds.0.alt_id') ) )
<p>
    You can fulfill this pledge by clicking here:
    @if( env('APP_ENVIROMENT') !== 'production' )
        <a href="{{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($campaign, 'getAltIds.0.alt_id') ) }}">
            {{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($campaign, 'getAltIds.0.alt_id') ) }}
        </a>
    @else
    <a href="{{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($campaign, 'getAltIds.0.alt_id') ) }}">
        {{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($campaign, 'getAltIds.0.alt_id') ) }}
    </a>
    @endif
</p>
@elseif( !is_null( array_get($chart, 'getAltIds.0.alt_id') ) )
<p>
    You can fulfill this pledge by clicking here:
    @if( env('APP_ENVIROMENT') !== 'production' )
        <a href="{{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($chart, 'getAltIds.0.alt_id') ) }}">
            {{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($chart, 'getAltIds.0.alt_id') ) }}
        </a>
    @else
    <a href="{{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($chart, 'getAltIds.0.alt_id') ) }}">
        {{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($chart, 'getAltIds.0.alt_id') ) }}
    </a>
    @endif
</p>
@endif
<p style="text-align: center;">
    <strong>@lang('Thank you very much')</strong>
</p>