<?php

namespace Tests\Unit\CRM;

use App\CRM\Listeners\TrackDocumentExport;
use App\CRM\Service\CRMService;
use App\CRM\Service\HubSpotCRMService;
use App\CRM\Service\SalesforceCRMService;
use App\Events\ExportedDocument;
use App\Http\Controllers\DocumentController;
use App\Http\Resources\SpecificationDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;
use Tests\Traits\AssertsCRMHandlesEvents;
use Tests\Traits\AssertsHubspotCRMHandlesEvents;
use Tests\Traits\AssertsSalesforceCRMHandlesEvents;

class TrackDocumentExportTest extends TestCase
{
    use AssertsCRMHandlesEvents;
    use AssertsSalesforceCRMHandlesEvents;
    use AssertsHubspotCRMHandlesEvents;

    protected function createDocument(User $user): SpecificationDocument
    {
        $outputDir = storage_path(DocumentController::EXPORT_PATH);
        if (! is_dir($outputDir)) {
            if (! mkdir($outputDir) && ! is_dir($outputDir)) {
                throw new RuntimeException("Directory '{$outputDir}' was not created");
            }
        }

        $document = new SpecificationDocument($outputDir, $user, []);
        $document->save();

        return $document;
    }

    /**
     * @test
     */
    public function it_check_if_listener_is_registered()
    {
        Event::fake();
        Event::assertListening(ExportedDocument::class, TrackDocumentExport::class);
    }

    /**
     * @test
     */
    public function it_send_mail_to_lead_mail()
    {
        // Given is a user
        $user = User::factory()->create(['role' => Role::USER, 'crm_user_contact_id' => 'xyz']);
        // And an exported document
        $document = $this->createDocument($user);
        $event = new ExportedDocument($user, $document);

        // We expect service is called from event
        $this->assertHubspotCRMServiceHandlesExportedDocument($this->mock(HubSpotCRMService::class), $user);
        $this->assertSalesforceCRMServiceHandlesExportedDocument($this->mock(SalesforceCRMService::class), $user);
        $this->assertCRMServiceHandlesExportedDocument($this->mock(CRMService::class), $user);

        $listener = $this->app->make(TrackDocumentExport::class);
        // When we handle event
        $listener->handle($event);
    }
}
