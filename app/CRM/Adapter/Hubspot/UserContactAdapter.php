<?php

namespace App\CRM\Adapter\Hubspot;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class UserContactAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'firstname' => 'first_name',
        'lastname'  => 'last_name',
        'email'     => 'email',
        'company'   => 'company',
    ];

    public function toCreateRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [];
        foreach (self::PROPERTY_MAP as $propertyName => $attributeKey) {
            $properties[$propertyName] = $user->$attributeKey;
        }
        $properties = array_merge($properties, $customProperties);

        return [
            'properties' => $properties,
        ];
    }
}
