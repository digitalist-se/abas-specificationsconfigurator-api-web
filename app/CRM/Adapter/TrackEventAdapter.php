<?php

namespace App\CRM\Adapter;

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

    public function toCreateRequestBody(User $user): array
    {
        return [
            'eventName'  => $this->eventName,
            'objectType' => 'contacts',
            'objectId'   => $user->crm_contact_id,
        ];
    }
}
