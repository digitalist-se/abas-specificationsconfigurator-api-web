<?php

namespace App\CRM\Adapter;

use App\Models\User;

class CompanyAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'name'    => 'company_name',
        'country' => 'lead_country',
        'website' => 'website',
        'zip'     => 'zipcode',
        'city'    => 'city',
        'address' => 'full_street',
    ];

    public function toCreateRequestBody(User $user): array
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
