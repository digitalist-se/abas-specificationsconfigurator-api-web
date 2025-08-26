<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Enums\ContactType;
use App\Models\User;

class ContactAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = [], ContactType $contactType = ContactType::User): array
    {
        $properties = [
            'Salutation' => $user->getContactSalutation($contactType),
            'FirstName'  => $user->getContactFirstName($contactType),
            'LastName'   => $user->getContactLastName($contactType),
            'Email'      => $user->getContactEmail($contactType),
            'Title'      => $user->getContactFunction($contactType),
        ];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
