<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class TaskAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
