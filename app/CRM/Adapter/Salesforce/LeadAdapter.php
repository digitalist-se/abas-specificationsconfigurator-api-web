<?php

namespace App\CRM\Adapter\Salesforce;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class LeadAdapter implements Adapter
{
    public function toRequestBody(User $user, array $customProperties = []): array
    {
        $properties = [
            'FirstName' => $user->first_name,
            'LastName'  => $user->last_name,
            'Company'   => $user->company,
            'Email'     => $user->email,
        ];

        return array_merge($properties, $customProperties);
    }
}
