<?php

namespace App\CRM\Adapter\Hubspot;

use App\CRM\Adapter\Adapter;
use App\Models\User;

class TrackEventAdapter implements Adapter
{
    protected string $eventName;

    /**
     * @param string $eventName
     */
    public function __construct(string $eventName)
    {
        $this->eventName = $eventName;
    }

    public function toRequestBody(User $user): array
    {
        return [
            'eventName'  => $this->eventName,
            'objectType' => 'contacts',
            'objectId'   => $user->crm_user_contact_id,
        ];
    }
}
