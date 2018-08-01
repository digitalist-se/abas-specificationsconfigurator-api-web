<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Lang;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeadRegisterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $leadUser;

    /**
     * Create a new message instance.
     *
     * @param $leadUser
     */
    public function __construct($leadUser)
    {
        $this->leadUser = $leadUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('email.lead-register')
            ->with('user', $this->leadUser)
            ->subject(Lang::get('email.lead.register.subject'));
    }
}
