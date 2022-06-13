<?php

namespace App\Listeners;

use App\Events\ExportedDocument;
use App\Mail\DocumentGeneratedMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendLeadOfDocumentExport
{

    /**
     * Handle the event.
     *
     * @param  ExportedDocument  $event
     * @return void
     */
    public function handle(ExportedDocument $event)
    {
        $mail = new DocumentGeneratedMail($event->user);
        $mail->attach($event->document->outputZipFilename());
        Mail::to(Config::get('mail.recipient.lead.address'))
            ->send($mail);
    }
}
