<?php

namespace Tests\Unit\CRM;

use App\CRM\Listeners\TrackDocumentExport;
use App\CRM\Service\CRMService;
use App\Events\ExportedDocument;
use App\Http\Controllers\DocumentController;
use App\Http\Resources\SpecificationDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class TrackDocumentExportTest extends TestCase
{
    protected function createDocument(User $user): SpecificationDocument
    {
        $outputDir = storage_path(DocumentController::EXPORT_PATH);
        if (! is_dir($outputDir)) {
            if (! mkdir($outputDir) && ! is_dir($outputDir)) {
                throw new RuntimeException("Directory '{$outputDir}' was not created");
            }
        }

        return new SpecificationDocument($outputDir, $user, []);
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
        $user = User::factory()->create(['role' => Role::USER]);
        // And an exported document
        $document = $this->createDocument($user);

        // We expect service is called from event
        $this->mock(CRMService::class)
            ->expects('trackDocumentExport')
            ->withArgs(fn (User $givenUser) => $givenUser->id === $user->id)
            ->andReturn(true);

        $listener = $this->app->make(TrackDocumentExport::class);
        // When we handle event
        $listener->handle(new ExportedDocument($user, $document));
    }
}
