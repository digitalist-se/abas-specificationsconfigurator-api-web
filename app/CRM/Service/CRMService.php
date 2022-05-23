<?php

namespace App\CRM\Service;

use App\Models\User;

interface CRMService
{
    public function createCompany(User $user);

    public function createContact(User $user);

    public function updateCompany(User $user);

    public function updateContact(User $user);

    public function linkContactToCompany(User $user);

    public function deleteCompany(User $user);

    public function deleteContact(User $user);
}
