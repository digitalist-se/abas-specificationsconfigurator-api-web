<?php

namespace App\CRM\Enums;

enum SalesforceObjectType: string
{
    case Lead = 'Lead';
    case Contact = 'Contact';
    case Account = 'Account';
}
