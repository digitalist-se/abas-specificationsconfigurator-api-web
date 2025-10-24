<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Enums\ContactType;
use App\Models\User;

class DefaultAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = [], ContactType $contactType = ContactType::User): array
    {
        $properties = [];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
