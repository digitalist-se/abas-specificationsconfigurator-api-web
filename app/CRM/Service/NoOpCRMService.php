<?php

namespace App\CRM\Service;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class NoOpCRMService implements CRMService
{
    public function createContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function updateContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function upsertContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function deleteContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function updateCompany(User $user): bool
    {
        return true;
    }

    public function trackDocumentExport(ExportedDocument $event): bool
    {
        return true;
    }

    public function trackUserRegistered(Registered $event): bool
    {
        return true;
    }
}
