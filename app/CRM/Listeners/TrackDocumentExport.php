<?php

namespace App\CRM\Listeners;

use App\CRM\Facades\CRM;
use App\Enums\ContactType;
use App\Events\ExportedDocument;

class TrackDocumentExport
{
    /**
     * Handle the event.
     *
     * @param  ExportedDocument  $event
     * @return void
     */
    public function handle(ExportedDocument $event)
    {
        CRM::upsertContact($event->user, ContactType::User);
        CRM::updateCompany($event->user);
        CRM::upsertContact($event->user, ContactType::Company);

        CRM::trackDocumentExport($event);
    }
}
