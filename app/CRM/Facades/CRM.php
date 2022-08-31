<?php

namespace App\CRM\Facades;

use App\CRM\Service\CRMService;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static createCompany(User $user);
 * @method static createContact(User $user, ContactType $type);
 * @method static updateCompany(User $user);
 * @method static updateContact(User $user, ContactType $type);
 * @method static linkContactToCompany(User $user, ContactType $type);
 * @method static deleteCompany(User $user);
 * @method static deleteContact(User $user, ContactType $type);
 * @method static trackDocumentExport(ExportedDocument $user);
 */
class CRM extends Facade
{
    public static function getFacadeAccessor()
    {
        return CRMService::class;
    }
}
