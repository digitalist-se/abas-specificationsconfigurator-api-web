<?php

namespace Tests\Traits;

use App\Enums\ContactType;
use App\Events\ExportedDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Mockery\MockInterface;

trait AssertsSalesforceCRMHandlesEvents
{
    protected function assertSalesforceCRMServiceHandlesUserRegistered(MockInterface $crmService, array $userAttributes): void
    {
        $userPhone = Arr::get($userAttributes, 'phone');
        $staticAttributes = [
            'Product_Family__c' => 'ABAS',
            'Status'            => 'Pre Lead',
            'LeadSource'        => 'ERP Planner',
        ];
        $crmService
            ->shouldReceive('createLead')
            ->withArgs(fn (User $u, array $props) => $u->phone === $userPhone && $props === $staticAttributes)
            ->andReturn(true);
    }

    protected function assertSalesforceCRMServiceHandlesExportedDocument(MockInterface $crmService, User $user): void
    {
        // no-op for now
    }
}
