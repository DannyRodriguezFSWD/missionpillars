<?php

namespace App\Traits;

use App\Classes\MissionPillarsLog;

use function GuzzleHttp\json_encode;

use Illuminate\Support\Facades\Mail;

trait SendsCustomerServiceEmails {
    
    public function sendCustomerServiceEmail($subject, $email_message, $options = [])
    {
        $user = null;
        extract($options); 
        $options['content'] = $email_message;
        $email = env('APP_CUSTOMER_SERVICE_EMAIL');
        
        if(empty($email)){
            MissionPillarsLog::log([
                'event'=>'.env file',
                'message'=>'.env missing APP_CUSTOMER_SERVICE_EMAIL'
            ]);
            return;
        }
        
         if (!$user && auth()->user()) {
            $user = auth()->user();
            $options['tenant'] = $user->tenant;
        }
        
        try {
            //you can find this email in logs table where event = mailgun
            Mail::send('emails.send.system', $options, 
            function($message) use($email, $user, $email_message, $subject, $options) {
                $tenant = ($user) ? $user->tenant : null;
                
                $response = $message
                ->to($email, null)
                ->subject($subject);
                if (!$user) {
                    MissionPillarsLog::log([
                        'event'=>'mailgun',
                        'message'=>'Customer service email sent without an authenticated user',
                    ]);
                } else {
                    if (array_get($options, 'replyTo.email')) {
                        $replyToName = array_get($options, 'replyTo.name');
                        $replyToEmail = array_get($options, 'replyTo.email');
                    } else {
                        $replyToName = implode(' ', [array_get($tenant, 'first_name'), array_get($tenant, 'last_name')]);
                        $replyToEmail = array_get($tenant, 'email');
                    }
                    
                    $response = $response
                    ->from(config('mail.from.address'), implode(' ', [
                        array_get($user, 'name'),
                        array_get($user, 'last_name')
                    ]))
                    ->replyTo($replyToEmail, $replyToName);
                }
                
                MissionPillarsLog::externalApiRequest(json_encode($tenant), $email_message, json_encode($response), 'mailgun');
            });
        } catch (\Throwable $th) {
            MissionPillarsLog::exception($th, json_encode($tenant), 'mailgun');
        }
    }
}
