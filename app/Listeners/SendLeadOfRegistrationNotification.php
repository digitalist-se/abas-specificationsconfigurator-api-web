<?php

namespace App\Listeners;

use App\Mail\LeadRegisterMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendLeadOfRegistrationNotification
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $recipients = Config::get('mail.recipient.lead.address');

        Mail::to([$recipients => $recipients])
            ->send(new LeadRegisterMail($event->user));
    }
}
