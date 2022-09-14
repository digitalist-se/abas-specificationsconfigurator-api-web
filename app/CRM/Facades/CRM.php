<?php

namespace App\CRM\Facades;

use App\CRM\Service\CRMService;
use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Facade;

class CRM extends Facade
{
    public static function getFacadeAccessor()
    {
        return CRMService::class;
    }
}
