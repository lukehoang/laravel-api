<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailtrapExample extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@nailpocket.com', 'NailPocket')
        ->subject('Email Confirmation')
        ->markdown('emails.mail')
        ->with([
            'name' => 'Luke',
            'link' => 'https://mailtrap.io/inboxes'
        ]);
    }
}
