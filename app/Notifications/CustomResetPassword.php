<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification {

    use Queueable;

    public $token;
    public $link;
    public $tenant;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $link, $tenant) {
        $this->token = $token;
        $this->link = $link;
        $this->tenant = $tenant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        $data = [
            'link' => $this->link,
            'reset' => url(route('password.reset', $this->token, false)),
            'organization' => array_get($this->tenant, 'organization')
        ];
        
        return (new MailMessage)
                        ->view('emails.send.resetpassword', $data)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->subject(__('Reset Password'));
                        //->line('You are receiving this email because we received a password reset request for your account.')
                        //->action('Reset Password', url($this->link . route('password.reset', $this->token, false)))
                        //->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
                //
        ];
    }

}
