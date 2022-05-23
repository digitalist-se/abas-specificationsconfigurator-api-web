<?php

namespace App\CRM\Service;

use App\Models\User;

class NoOpCRMService implements CRMService
{
    public function createCompany(User $user)
    {
        return true;
    }

    public function createContact(User $user)
    {
        return true;
    }

    public function updateCompany(User $user)
    {
        return true;
    }

    public function updateContact(User $user)
    {
        return true;
    }

    public function linkContactToCompany(User $user)
    {
        return true;
    }

    public function deleteCompany(User $user)
    {
        return true;
    }

    public function deleteContact(User $user)
    {
        return true;
    }
}
