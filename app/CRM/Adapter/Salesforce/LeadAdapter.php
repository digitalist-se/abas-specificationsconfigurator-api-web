<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Enums\ContactType;
use App\Models\User;

class LeadAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = [], ContactType $contactType = ContactType::User): array
    {
        $properties = [
            'Salutation' => $user->getContactSalutation($contactType),
            'FirstName'  => $user->getContactFirstName($contactType),
            'LastName'   => $user->getContactLastName($contactType),
            'Company'    => $user->getContactCompany($contactType),
            'Email'      => $user->getContactEmail($contactType),
            'Title'      => $user->getContactFunction($contactType),
            'Street'     => $user->full_street,
            'PostalCode' => $user->zipcode,
            'City'       => $user->city,
            'Country'    => $user->country ? $user->leadCountry : null,
            'Phone'      => $user->phone,
            'Website'    => $user->website,
        ];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
