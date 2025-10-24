<?php

namespace App\CRM\Enums;

enum SalesforceContentDocumentLinkVisibility: string
{
    case AllUsers = 'AllUsers';
    case StandardUsers = 'InternalUsers';
    case SharedUsers = 'SharedUsers';
}
