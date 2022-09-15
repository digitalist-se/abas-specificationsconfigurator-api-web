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
        $expectUserAndContactType = fn (User $user, ContactType $type) => $user->phone === $userPhone && $type === ContactType::User;
        $crmService
            ->shouldReceive('upsertContact')
            ->withArgs($expectUserAndContactType)
            ->andReturn(true);

        $expectEvent = fn (Registered $event) => ($user = $event->user) instanceof User && $userPhone === $user->phone;
        $crmService->shouldReceive('trackUserRegistered')
            ->withArgs($expectEvent)
            ->andReturn(true);
    }

    protected function assertCRMServiceHandlesExportedDocument(MockInterface $crmService, User $user): void
    {
        $expectUser = fn (User $u) => $u->id === $user->id;
        $expectUserAndContactType = fn (ContactType $contactType) => fn (User $u, ContactType $type) => $u->id === $user->id && $type === $contactType;
        $crmService
            ->shouldReceive('upsertContact')
            ->withArgs($expectUserAndContactType(ContactType::User))
            ->andReturn(true);

        $crmService
            ->shouldReceive('updateCompany')
            ->withArgs($expectUser)
            ->andReturn(true);

        $crmService
            ->shouldReceive('upsertContact')
            ->withArgs($expectUserAndContactType(ContactType::Company))
            ->andReturn(true);

        $expectEvent = fn (ExportedDocument $event) => $event->user->id === $user->id;
        $crmService->shouldReceive('trackDocumentExport')
            ->withArgs($expectEvent)
            ->andReturn(true);
    }
}
