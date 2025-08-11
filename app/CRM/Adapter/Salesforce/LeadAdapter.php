<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class LeadAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'firstname' => 'first_name',
        'lastname'  => 'last_name',
        'email'     => 'email',
        'company'   => 'company',
    ];

    public function toCreateRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [
            'FirstName' => $user->first_name,
            'LastName'  => $user->last_name,
            'Company'   => $user->company,
            'Email'     => $user->email,
            'Country'   => $user->leadCountry,
        ];

        return array_merge($properties, $customProperties);
    }
}
