<p>Hi, {{ array_get($contact, 'full_name') }}</p>

<p>We just wanted to let you know that {{ array_get($contact, 'tenant.organization') }} has re-subscribed you to their awesome email updates!</p>

<p>If this is what you wanted, then we are all good.</p>

<p>If you do not want to be re-subscribed, then please click the button below to be permanently unsubscribed. (They will not be able to re-subscribe you.)</p>

<p><a href="{{ route('contacts.perm-unsubscribe', $contact) }}" target="_blank" class="button button-green">Permanently Unsubscribe</a></p>

<p>Thank you</p>

<p>
    The Continue To Give team<br>
    <a href="{{ env('C2G_MAIN_URL') }}">www.ContinueToGive.com</a>
</p>
