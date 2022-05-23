<?php

namespace App\CRM\Adapter;

use App\Models\User;

class CompanyAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'name'    => 'company_name',
        'country' => 'lead_country',
    ];

    public function toCreateRequestBody(User $user): array
    {
        $properties = [];
        foreach (self::PROPERTY_MAP as $propertyName => $attributeKey) {
            $properties[$propertyName] = $user->$attributeKey;
        }
        $properties['address'] = $user->full_street;

        return [
            'properties' => $properties,
        ];
    }
}
