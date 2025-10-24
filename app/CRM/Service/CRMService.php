<?php

namespace App\CRM\Service;

use App\Events\ExportedDocument;
use Illuminate\Auth\Events\Registered;

interface CRMService
{
    public function handleUserRegistered(Registered $event): bool;

    public function handleDocumentExport(ExportedDocument $event): bool;
}
