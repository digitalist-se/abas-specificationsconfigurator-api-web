<?php

namespace App\CRM\Service;

use App\Models\User;

interface CRMService
{
    public function createCompany(User $user): bool;

    public function createContact(User $user): bool;

    public function updateCompany(User $user): bool;

    public function updateContact(User $user): bool;

    public function linkContactToCompany(User $user): bool;

    public function deleteCompany(User $user): bool;

    public function deleteContact(User $user): bool;

    public function trackDocumentExport(User $user): bool;
}
