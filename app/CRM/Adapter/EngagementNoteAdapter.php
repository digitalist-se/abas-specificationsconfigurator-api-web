<?php

namespace App\CRM\Adapter;

use App\Models\User;

class EngagementNoteAdapter
{
    public function toCreateRequestBody($attachmentId, string $body, ?string $contactId, ?string $companyId): array
    {
        return [
            'engagement' => [
                'active' => true,
                'type'   => 'NOTE',
            ],
            'associations' => [
                'contactIds' => $contactId ? [$contactId] : [],
                'companyIds' => $companyId ? [$companyId] : [],
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
