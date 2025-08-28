<?php

namespace App\Console\Commands\Salesforce;

enum EventType: string
{
    case DocumentExport = 'exported';
    case UserRegistered = 'registered';
}
