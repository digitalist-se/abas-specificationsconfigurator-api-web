<?php

namespace App\CRM\Enums;

enum SalesforceObjectType: string
{
    case Lead = 'Lead';
    case Contact = 'Contact';
    case Account = 'Account';
    case Task = 'Task';
    case ContentVersion = 'ContentVersion';
    case ContentDocument = 'ContentDocument';
    case ContentDocumentLink = 'ContentDocumentLink';
}
