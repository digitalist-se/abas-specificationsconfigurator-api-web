<?php

namespace App\CRM\Service;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;

class NoOpCRMService implements CRMService
{
    public function createCompany(User $user): bool
    {
        return true;
    }

    public function createContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function updateCompany(User $user): bool
    {
        return true;
    }

    public function updateContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function linkContactsToCompany(User $user, ContactType $type): bool
    {
        return true;
    }

    public function deleteCompany(User $user): bool
    {
        return true;
    }

    public function deleteContact(User $user, ContactType $type): bool
    {
        return true;
    }

    public function trackDocumentExport(ExportedDocument $event): bool
    {
        return true;
    }
}
