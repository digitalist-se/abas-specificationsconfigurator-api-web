<?php

namespace Tests\Unit\CRM;

use App\CRM\Facades\CRM;
use App\CRM\Service\CRMService;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HubSpotCRMServiceTest extends TestCase
{
    protected string $expectedEventName = 'random-event-name';

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Config::set('services.hubSpot.enabled', true);
        Config::set('services.hubSpot.events.document-export', $this->expectedEventName);
    }

    /**
     * @test
     */
    public function it_can_not_track_document_export_without_crm_user()
    {
        $user = User::factory()->make();
        $this->app->make(CRMService::class);
        $service = $this->app->make(CRMService::class);
        $service->trackDocumentExport($user);
        Http::assertNothingSent();
    }

    /**
     * @test
     */
    public function it_can_track_document_export()
    {
        $user = User::factory()->make([
            'crm_contact_id' => 'xyz',
        ]);
        $service = $this->app->make(CRMService::class);
        $service->trackDocumentExport($user);
        Http::assertSent(function (?Request $request, ?Response $response) {
            $this->assertEquals('/events/v3/send', $request->toPsrRequest()->getUri()->getPath());

            $this->assertEquals(
                [
                    'eventName'  => $this->expectedEventName,
                    'properties' => [],
                    'objectType' => 'contacts',
                    'objectId'   => 'xyz',
                ],
                $request->data());

            return true;
        });
    }
}
