<h4>Hi {{ array_get($tenant, 'organization') }}</h4>
<p>
    @lang('Please use the link below to see your latest  invoice. Thank you for being such a great customer')!
</p>

<p>
    <a href="{{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}crm/settings/subscription/invoices/info">
        {{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}crm/settings/subscription/invoices/info
    </a>
</p>
<p>&nbsp;</p>
<p>
    @lang('If you have any questions please contact us at')<br/>
    <a href="mailto:{{ env('APP_CUSTOMER_SERVICE_EMAIL') }}">{{ env('APP_CUSTOMER_SERVICE_EMAIL') }}</a><br/>
    - The Mission Pillars Team
</p>

<p>
    <small>This email has been sent in relation to your account</small>
</p>