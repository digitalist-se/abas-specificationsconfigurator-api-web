<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class ContactAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [
            'Salutation' => $user->salutation,
            'FirstName'  => $user->contact_first_name ?? $user->first_name,
            'LastName'   => $user->contact_last_name ?? $user->last_name,
            'Email'      => $user->contact_email ?? $user->email,
            'Title'      => $user->contact_function,
        ];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
