<?php

namespace App\CRM\Adapter;

use App\Models\User;

class ContactAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'name' => 'first_name',
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
