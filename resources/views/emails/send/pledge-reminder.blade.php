<h4>Dear [:salutation:] [:preferred-name:]</h4>
<p>
    Just a friendly reminder for your{{ array_get($template, 'is_recurring') ? ' recurring ':' ' }}pledge of ${{ array_get($pledge, 'amount') }} to {{ array_get($campaign, 'id', 1) > 1 ? array_get($campaign, 'name') : array_get($chart, 'name') }}, with a promised pay date of {{ humanReadableDate(array_get($template, 'billing_start_date')) }}.
</p>
<p>
    The remaining balance is ${{ array_get($pledge, 'amount') }}
</p>

@if( !is_null($campaignAltID) )
<p>
    You can fulfill this pledge by clicking here: 
    @if( env('APP_ENVIROMENT') !== 'production' )
        <a href="{{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($campaignAltID, 'alt_id') ) }}">
            {{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($campaignAltID, 'alt_id') ) }}
        </a>
    @else
    <a href="{{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($campaignAltID, 'alt_id') ) }}">
        {{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($campaignAltID, 'alt_id') ) }}
    </a>
    @endif
</p>
@elseif( !is_null($chartAltID) )
<p>
    You can fulfill this pledge by clicking here: 
    @if( env('APP_ENVIROMENT') !== 'production' )
        <a href="{{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($chartAltID, 'alt_id') ) }}">
            {{ sprintf('https://%s.continuetogive.com/%s/donation_prompt/', env('APP_ENVIROMENT'), array_get($chartAltID, 'alt_id') ) }}
        </a>
    @else
    <a href="{{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($chartAltID, 'alt_id') ) }}">
        {{ sprintf('https://continuetogive.com/%s/donation_prompt/', array_get($chartAltID, 'alt_id') ) }}
    </a>
    @endif
</p>
@endif

<p style="text-align: center;">
    <strong>@lang('Thank you very much')</strong>
</p>