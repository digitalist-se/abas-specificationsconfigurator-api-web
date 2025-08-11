<?php

namespace App\CRM\Service;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

interface CRMService
{
    public function createContact(User $user, ContactType $type, array $customProperties = []): bool;

    public function updateContact(User $user, ContactType $type, array $customProperties = []): bool;

    public function deleteContact(User $user, ContactType $type): bool;

    public function upsertContact(User $user, ContactType $type, array $customProperties = []): bool;

    public function updateCompany(User $user): bool;

    public function trackDocumentExport(ExportedDocument $event): bool;

    public function trackUserRegistered(Registered $event): bool;

    public function handleUserRegistered(Registered $event): bool;

    public function handleDocumentExport(ExportedDocument $event): bool;
}
