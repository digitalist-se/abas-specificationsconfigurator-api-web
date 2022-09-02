<?php

namespace App\CRM\Enums;

enum HubSpotEventType: string
{
    case DocumentExport = 'document-export';
    case UserRegistered = 'user-registered';
}
