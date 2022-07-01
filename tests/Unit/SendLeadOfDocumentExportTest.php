<?php

namespace Tests\Unit;

use App\Events\ExportedDocument;
use App\Http\Controllers\DocumentController;
use App\Http\Resources\SpecificationDocument;
use App\Listeners\SendLeadOfDocumentExport;
use App\Mail\DocumentGeneratedMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SendLeadOfDocumentExportTest extends TestCase
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
        Event::assertListening(ExportedDocument::class, SendLeadOfDocumentExport::class);
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

        Mail::fake();
        $listener = $this->app->make(SendLeadOfDocumentExport::class);
        // When we handle event
        $listener->handle(new ExportedDocument($user, $document));

        // We expect a mail is sent to lead
        Mail::assertQueued(DocumentGeneratedMail::class, function (DocumentGeneratedMail $mail) use ($user) {
            /*
             * @var DocumentGeneratedMail $mail
             */
            $this->assertEquals(config('mail.recipient.lead.address'), $mail->to[0]['address']);
            $this->assertNotEmpty($mail->attachments, 'Genereted Document was not sended in mail. mail has no attachments');

            return $mail->user->id === $user->id;
        });
    }
}
