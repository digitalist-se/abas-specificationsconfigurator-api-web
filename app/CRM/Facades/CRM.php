<?php

namespace App\CRM\Facades;

use App\CRM\Service\CRMService;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Facade;

/**
 * @method static createContact(User $user, ContactType $type): bool
 * @method static updateContact(User $user, ContactType $type): bool
 * @method static deleteContact(User $user, ContactType $type): bool
 * @method static upsertContact(User $user, ContactType $type): bool
 * @method static updateCompany(User $user): bool
 * @method static trackDocumentExport(ExportedDocument $event): bool
 * @method static trackUserRegistered(Registered $event): bool
 */
class CRM extends Facade
{
    public static function getFacadeAccessor()
    {
        return CRMService::class;
    }
}
