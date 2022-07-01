<?php

namespace Tests\Unit\CRM;

use App\CRM\Service\CRMService;
use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
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
        Config::set('services.hubSpot.enabled', true);
        Config::set('services.hubSpot.events.document-export', $this->expectedEventName);
    }

    /**
     * @test
     */
    public function it_can_not_track_document_export_without_crm_user()
    {
        Http::fake();
        $user = User::factory()->make();
        $document = $this->givenIsASpecificationDocument($user);
        $this->app->make(CRMService::class);
        $service = $this->app->make(CRMService::class);
        $service->trackDocumentExport(new ExportedDocument($user, $document));
        Http::assertNothingSent();
    }

    /**
     * @test
     */
    public function it_can_track_document_export()
    {
        Http::fake([
            '*' => Http::sequence([
                Http::response(['id' => 'fakeId']),
                Http::response(['id' => 'fakeId']),
                Http::response(['id' => 'fakeId']),
            ]),
        ]);
        $expectedFolderId = 3001;
        Config::set('services.hubSpot.folder.id', $expectedFolderId);
        $user = $this->givenIsAUserWithCrmId();
        $document = $this->givenIsASpecificationDocument($user);

        $service = $this->app->make(CRMService::class);
        $service->trackDocumentExport(new ExportedDocument($user, $document));

        Http::assertSent(function (?Request $request, ?Response $response) use ($expectedFolderId) {
            if ('/files/v3/files' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }
            $this->assertTrue($request->hasFile('file'));

            $data = collect($request->data())->flatMap(function ($part) {
                switch ($part['name']) {
                    case 'options':
                        $part['contents'] = json_decode($part['contents'], true);
                    case 'folderId':
                        return [
                            $part['name'] => $part['contents'],
                        ];
                }

                return [];
            });
            $this->assertEquals(
                [
                    'folderId' => $expectedFolderId,
                    'options'  => [
                        'access'                      => 'PRIVATE',
                        'overwrite'                   => false,
                        'duplicateValidationStrategy' => 'none',
                        'duplicateValidationScope'    => 'EXACT_FOLDER',
                    ],
                ],
                $data->toArray());

            return true;
        });
        Http::assertSent(function (?Request $request, ?Response $response) {
            if ('/events/v3/send' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals(
                [
                    'eventName'  => $this->expectedEventName,
                    'objectType' => 'contacts',
                    'objectId'   => 'xyz',
                ],
                $request->data());

            return true;
        });
        Http::assertSent(function (?Request $request, ?Response $response) {
            if ('/engagements/v1/engagements' !== $request->toPsrRequest()->getUri()->getPath()) {
                return false;
            }

            $this->assertEquals(
                [
                    'active' => true,
                    'type'   => 'NOTE',
                ],
                $request->data()['engagement']);

            $this->assertEquals(
                [
                    'contactIds' => [
                        'xyz',
                    ],
                ],
                $request->data()['associations']);

            $this->assertEquals(
                [
                    [
                        'id' => 'fakeId',
                    ],
                ],
                $request->data()['attachments']);
            $this->assertStringStartsWith('Neue Lastenheftgenerierung:', $request->data()['metadata']['body']);

            return true;
        });

        unlink($document->outputZipFilename());
    }

    /**
     * @return User
     */
    private function givenIsAUserWithCrmId(): mixed
    {
        return User::factory()->make([
            'crm_contact_id' => 'xyz',
        ]);
    }

    /**
     * @param mixed $user
     *
     * @return \App\Http\Resources\SpecificationDocument
     * @throws \App\Exceptions\GenerateExcelException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function givenIsASpecificationDocument(mixed $user): SpecificationDocument
    {
        $outputDir = storage_path('app/export');
        $document = new SpecificationDocument($outputDir, $user, []);
        $document->save();

        return $document;
    }
}
