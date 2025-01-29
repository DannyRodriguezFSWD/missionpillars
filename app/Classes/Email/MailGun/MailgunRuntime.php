<?php

namespace App\Classes\Email\Mailgun;

use App\Models\Mailgun;

/**
 * Description of MailgunRuntime
 *
 * @author josemiguel
 */
class MailgunRuntime {

    public static function setOutgoingDomain($tenant_id) {
        $mailgun = Mailgun::withoutGlobalScopes()->where('tenant_id', $tenant_id)->first();
        if(!is_null($mailgun)){
            config(['mail.driver' => 'mailgun']);
            config(['services.mailgun.domain' => array_get($mailgun, 'domain', env('MAILGUN_DOMAIN'))]);
            config(['services.mailgun.secret' => array_get($mailgun, 'secret', env('MAILGUN_SECRET'))]);

            $app = \App::getInstance();

            $app->singleton('swift.transport', function ($app) {
                return new \Illuminate\Mail\TransportManager($app);
            });

            $mailer = new \Swift_Mailer($app['swift.transport']->driver());
            \Mail::setSwiftMailer($mailer);
        }
    }

}
