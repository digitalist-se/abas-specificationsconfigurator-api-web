<?php

namespace Tests\Traits;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Mockery\MockInterface;

trait AssertsCRMHandlesEvents
{
    protected function assertCRMServiceHandlesUserRegistered(MockInterface $crmService, array $userAttributes): void
    {
        $userPhone = Arr::get($userAttributes, 'phone');
        $crmService
            ->shouldReceive('handleUserRegistered')
            ->once()
            ->withArgs(fn (Registered $event) => ($user = $event->user) instanceof User && $userPhone === $user->phone)
            ->andReturn(true);
    }

    protected function assertCRMServiceHandlesExportedDocument(MockInterface $crmService, User $user): void
    {
        $crmService
            ->shouldReceive('handleDocumentExport')
            ->once()
            ->withArgs(fn (ExportedDocument $event) => $event->user->id === $user->id)
            ->andReturn(true);
    }
}
