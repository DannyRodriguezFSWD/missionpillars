<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'twilio/integration/track/webhook',
        'twilio/integration/reply/webhook',
        'sms/reply/*',
        'ajax/public/contacts/autocomplete',
        'join/*/join',
        'redirect',
        'tiny/jwt'
    ];
}
