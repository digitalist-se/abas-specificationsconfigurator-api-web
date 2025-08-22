<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class AccountAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [
            'Name'              => $user->company,
            'BillingStreet'     => $user->full_street,
            'BillingPostalCode' => $user->zipcode,
            'BillingCity'       => $user->city,
            'BillingCountry'    => $user->country ? $user->leadCountry : null,
            'Phone'             => $user->phone,
            'Website'           => $user->website,
        ];

        $filteredProperties = array_filter($properties, fn ($value) => ! is_null($value) && $value !== '');

        return array_merge($filteredProperties, $customProperties);
    }
}
