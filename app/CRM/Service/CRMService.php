<?php

namespace App\CRM\Service;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;

interface CRMService
{
    public function createCompany(User $user): bool;

    public function createContact(User $user, ContactType $type): bool;

    public function updateCompany(User $user): bool;

    public function updateContact(User $user, ContactType $type): bool;

    public function linkContactsToCompany(User $user, ContactType $type): bool;

    public function deleteCompany(User $user): bool;

    public function deleteContact(User $user, ContactType $type): bool;

    public function trackDocumentExport(ExportedDocument $event): bool;
}
