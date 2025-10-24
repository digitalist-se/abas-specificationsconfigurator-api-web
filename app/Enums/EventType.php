<?php

namespace App\Enums;

enum EventType: string
{
    case DocumentExport = 'document-export';
    case UserRegistration = 'user-registration';
}
