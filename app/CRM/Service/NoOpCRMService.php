<?php

namespace App\CRM\Service;

use App\Models\User;

class NoOpCRMService implements CRMService
{
    public function createCompany(User $user): bool
    {
        return true;
    }

    public function createContact(User $user): bool
    {
        return true;
    }

    public function updateCompany(User $user): bool
    {
        return true;
    }

    public function updateContact(User $user): bool
    {
        return true;
    }

    public function linkContactToCompany(User $user): bool
    {
        return true;
    }

    public function deleteCompany(User $user): bool
    {
        return true;
    }

    public function deleteContact(User $user): bool
    {
        return true;
    }

    public function trackDocumentExport(User $user): bool
    {
        return true;
    }
}
