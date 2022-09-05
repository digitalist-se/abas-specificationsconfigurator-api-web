<?php

namespace App\CRM\Adapter;

use App\Models\User;

class EngagementNoteAdapter
{
    public function toCreateRequestBody(User $user, $attachmentId, string $body): array
    {
        return [
            'engagement' => [
                'active' => true,
                'type'   => 'NOTE',
            ],
            'associations' => [
                'contactIds' => [
                    $user->crm_user_contact_id,
                ],
            ],
            'attachments' => [
                [
                    'id' => $attachmentId,
                ],
            ],
            'metadata' => [
                'body' => $body,
            ],
        ];
    }
}
