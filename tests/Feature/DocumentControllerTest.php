<?php

namespace Tests\Feature;

use App\Mail\DocumentGeneratedMail;
use App\Models\Answer;
use App\Models\Element;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\PassportTestCase;

class DocumentControllerTest extends PassportTestCase
{
    use WithFaker;
    protected $role = Role::USER;

    public function setUp()
    {
        parent::setUp();
        $this->deleteAllExportFilesOfUser();
        $allElements = Element::where('type', '=', 'text')->get();
        foreach ($allElements as $element) {
            Answer::create([
                'value'      => ['text' => $this->faker->text()],
                'element_id' => $element->id,
                'user_id'    => $this->user->id,
            ]);
        }
    }

    protected function tearDown()
    {
        $this->deleteAllExportFilesOfUser();
        parent::tearDown();
    }

    protected function deleteAllExportFilesOfUser()
    {
        $files = storage_path('app/export').'/'.$this->user->id.'_*';
        foreach (glob($files) as $filename) {
            unlink($filename);
        }
        $files = storage_path('app/tmp/PhpWord*');
        foreach (glob($files) as $filename) {
            unlink($filename);
        }
    }

    public function testGenerateDocumentWithoutPhone()
    {
        Mail::fake();
        $this->user->update([
            'phone' => '',
        ]);
        $response = $this->get('/api/document/generate');
        $this->assertStatus($response, 428);
    }

    public function testGenerateDocument()
    {
        Mail::fake();
        $response = $this->get('/api/document/generate');
        $this->assertStatus($response, 200);

        $user = $this->user;
        Mail::assertQueued(DocumentGeneratedMail::class, function ($mail) use ($user) {
            /*
             * @var DocumentGeneratedMail $mail
             */
            $this->assertEquals(config('mail.recipient.lead.address'), $mail->to[0]['address']);
            $this->assertNotEmpty($mail->attachments, 'Genereted Document was not sended in mail. mail has no attachments');

            return $mail->user->id === $user->id;
        });
    }
}
