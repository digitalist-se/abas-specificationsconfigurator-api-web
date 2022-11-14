<?php

namespace Tests\Feature;

use App\CRM\Service\CRMService;
use App\Events\ExportedDocument;
use App\Mail\DocumentGeneratedMail;
use App\Models\Answer;
use App\Models\ChoiceType;
use App\Models\Element;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\PassportTestCase;
use Tests\Traits\AssertsCRMHandlesEvents;

class DocumentControllerTest extends PassportTestCase
{
    use WithFaker;
    use AssertsCRMHandlesEvents;

    protected $role = Role::USER;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteAllExportFilesOfUser();
        $allElements = Element::where('type', '=', 'text')->get();
        foreach ($allElements as $element) {
            Answer::create([
                'value'      => ['text' => $this->faker->text(40)],
                'element_id' => $element->id,
                'user_id'    => $this->user->id,
            ]);
        }
        $choiceType = ChoiceType::where('multiple', '=', 1)->get()->first();
        $branchesElement = Element::where('choice_type_id', '=', $choiceType->id)->get()->first();
        Answer::create([
            'value' => [
                'options'      => ['branche.option.maschinen-und-anlagenbau.text'],
                'otherEnabled' => true,
                'otherValue'   => 'this is a other value',
            ],
            'element_id' => $branchesElement->id,
            'user_id'    => $this->user->id,
        ]);
    }

    protected function tearDown(): void
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
    }

    public function test_generate_document_without_phone()
    {
        Mail::fake();
        $this->user->update([
            'phone' => '',
        ]);
        $response = $this->get('/api/document/generate');
        static::assertStatus($response, 428);
    }

    public function test_generate_document()
    {
        $this->user->update(['crm_user_contact_id' => 'xyz']);
        $user = $this->user;
        Mail::fake();
        $this->assertCRMServiceHandlesExportedDocument($this->mock(CRMService::class), $user);
        $response = $this->get('/api/document/generate');
        static::assertStatus($response, 200);
        // We expect a lead mail for document generation is NOT sent anymore
        Mail::assertNotQueued(DocumentGeneratedMail::class);
    }
}
