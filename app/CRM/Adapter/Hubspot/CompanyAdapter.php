<?php

namespace App\CRM\Adapter\Hubspot;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class CompanyAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'name'    => 'company',
        'country' => 'lead_country',
        'website' => 'website',
        'zip'     => 'zipcode',
        'city'    => 'city',
        'address' => 'full_street',
    ];

    public function toRequestBody(User $user): array
    {
        $properties = [];
        foreach (self::PROPERTY_MAP as $propertyName => $attributeKey) {
            $properties[$propertyName] = $user->$attributeKey;
        }

        return [
            'properties' => $properties,
        ];
    }
}
