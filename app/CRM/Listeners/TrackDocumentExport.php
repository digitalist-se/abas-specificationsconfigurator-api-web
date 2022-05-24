<?php

namespace App\CRM\Listeners;

use App\CRM\Facades\CRM;
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
        CRM::trackDocumentExport($event->user);
    }
}
