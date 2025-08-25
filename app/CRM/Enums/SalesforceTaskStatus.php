<?php

namespace App\CRM\Enums;

enum SalesforceTaskStatus: string
{
    case Open = 'Open';
    case Completed = 'Completed';
}
