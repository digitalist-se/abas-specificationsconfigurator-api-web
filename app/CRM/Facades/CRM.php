<?php

namespace App\CRM\Facades;

use App\CRM\Service\CRMService;
use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static createCompany(User $user);
 * @method static createContact(User $user);
 * @method static updateCompany(User $user);
 * @method static updateContact(User $user);
 * @method static linkContactToCompany(User $user);
 * @method static deleteCompany(User $user);
 * @method static deleteContact(User $user);
 * @method static trackDocumentExport(User $user);
 */
class CRM extends Facade
{
    public static function getFacadeAccessor()
    {
        return CRMService::class;
    }
}
