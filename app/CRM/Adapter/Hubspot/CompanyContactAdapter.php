<?php

namespace App\CRM\Adapter\Hubspot;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class CompanyContactAdapter implements Adapter
{
    const PROPERTY_MAP = [
        'salutation' => 'salutation',
        'firstname'  => 'contact_first_name',
        'lastname'   => 'contact_last_name',
        'email'      => 'contact_email',
        'company'    => 'company',
        'phone'      => 'phone',
        'jobtitle'   => 'contact_function',
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
