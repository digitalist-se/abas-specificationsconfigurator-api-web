<?php

namespace App\CRM\Adapter;

use App\Models\User;

class EngagementNoteAdapter
{
    public function toCreateRequestBody($attachmentId, string $body, array $contactIds = [], array $companyIds = []): array
    {
        return [
            'engagement' => [
                'active' => true,
                'type'   => 'NOTE',
            ],
            'associations' => [
                'contactIds' => $contactIds,
                'companyIds' => $companyIds,
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
