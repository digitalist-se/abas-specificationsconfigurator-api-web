<?php

namespace App\CRM\Adapter;

use App\Models\User;

class EngagementNoteAdapter
{
    /**
     * @param array{contactIds:array<string|int>, companyIds:array<string|int>, dealIds: array<string|int>} $associations
     *
     * @return array
     */
    public function toCreateRequestBody($attachmentId, string $body, array $associations = []): array
    {
        return [
            'engagement' => [
                'active' => true,
                'type'   => 'NOTE',
            ],
            'associations' => $associations,
            'attachments'  => [
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
