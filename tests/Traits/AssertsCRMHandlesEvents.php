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
            ->shouldReceive('upsertContact')
            ->withArgs(fn (User $u, ContactType $type, array $props) => $u->phone === $userPhone && $type === ContactType::User && $props === ['erp_registration_trigger' => true])
            ->andReturn(true);

        $crmService->shouldReceive('trackUserRegistered')
            ->withArgs(fn (Registered $event) => ($user = $event->user) instanceof User && $userPhone === $user->phone)
            ->andReturn(true);
    }

    protected function assertCRMServiceHandlesExportedDocument(MockInterface $crmService, User $user): void
    {
        $crmService
            ->shouldReceive('upsertContact')
            ->withArgs(fn (User $u, ContactType $type, array $props) => $u->id === $user->id && $type === ContactType::User && $props === ['erp_lastenheft_trigger' => true])
            ->andReturn(true);

        $crmService
            ->shouldReceive('updateCompany')
            ->withArgs(fn (User $u) => $u->id === $user->id)
            ->andReturn(true);

        $crmService
            ->shouldReceive('upsertContact')
            ->withArgs(fn (User $u, ContactType $type, array $props) => $u->id === $user->id && $type === ContactType::Company && empty($props))
            ->andReturn(true);

        $expectEvent = fn (ExportedDocument $event) => $event->user->id === $user->id;
        $crmService->shouldReceive('trackDocumentExport')
            ->withArgs($expectEvent)
            ->andReturn(true);
    }
}
