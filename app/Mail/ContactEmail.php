<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Tenant;
use App\Constants;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;
    private $contact;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data =[
            'contact' => $this->contact,
            'link' => Constants::HTTP.array_get($this->contact, 'subdomain').'.'.Constants::MAIN_DOMAIN
        ];
        return $this->from(array_get($this->contact, 'from'))
                ->subject('New email from '. array_get($this->contact, 'organization'))
                ->markdown('emails.send.order')
                ->with($data);
    }
}
