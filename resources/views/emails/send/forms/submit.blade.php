@if (!is_null($manager))
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table class="inner-body" align="center" cellpadding="0" cellspacing="0">
                @if($form->custom_header)
                    <tr>
                        <td>
                            {!! $form->custom_header !!}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        
                        <p>Dear {{ array_get($manager, 'first_name') }}</p>
                        @if(!is_null(array_get($contact, 'first_name')))
                        <p>
                            You have a new form submission from 
                            {{ array_get($contact, 'first_name') }} 
                            {{ array_get($contact, 'last_name') }} 
                            @if (!is_null(array_get($contact, 'email_1')))
                                ({{ array_get($contact, 'email_1') }})
                            @endif
                        </p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>
                            You can manage this form from your admin panel here 
                            @if(!is_null($entry))
                            <a href="{{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}crm/entries/{{ array_get($entry, 'id') }}">
                                {{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}crm/entries/{{ array_get($entry, 'id') }}
                            </a>.
                            @endif
                        </p>
                        <p>You will also be able to check their answers{{ array_get($form, 'accept_payments') ? ' and payment information':'' }}.</p>
                        @if(array_get($form, 'accept_payments'))
                        <p>You will get another email when the user completes their payment.</p>
                        @endif
                        <p>Sincerely<br>The Mission Pillars &copy; Team</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@else
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table class="inner-body" align="center" cellpadding="0" cellspacing="0">
                @if($form->custom_header)
                    <tr>
                        <td>
                            {!! $form->custom_header !!}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <p>
                            Dear {{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}<br>
                            Thank you very much for submitting the <b>{{ array_get($form, 'name') }}</b> form.
                        </p>
                        @if(array_get($form, 'accept_payments') && $total > 0)
                            <p>You will get another email when you complete your payment.</p>
                            <p>
                                You can make your payment here
                                <a href="{{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}forms/{{ array_get($form, 'uuid') }}/payment?contact_id={{ array_get($contact, 'id') }}&entry_id={{ array_get($entry, 'id') }}&total={{ $total }}">
                                    {{ sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')) }}forms/{{ array_get($form, 'uuid') }}/payment?contact_id={{ array_get($contact, 'id') }}&entry_id={{ array_get($entry, 'id') }}&total={{ $total }}
                                </a>.
                            </p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>&nbsp;</p>
                        <p>Sincerely<br>{{ array_get($tenant, 'organization') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif
