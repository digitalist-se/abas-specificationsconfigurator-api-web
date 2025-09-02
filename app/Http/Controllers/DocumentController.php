<?php

namespace App\Http\Controllers;

use App\Events\ExportedDocument;
use App\Http\Resources\SpecificationDocument;
use App\Mail\DocumentGeneratedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class DocumentController extends Controller
{
    const EXPORT_PATH = 'app/export';

    /**
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate(Request $request)
    {
        $user = $request->user();
        if (! $user->hasAllRequiredFieldsForSpecificationDocument()) {
            return response('user profile data missing', 428);
        }
        $outputDir = storage_path('app/export');
        if (! is_dir($outputDir)) {
            if (! mkdir($outputDir) && ! is_dir($outputDir)) {
                throw new RuntimeException("Directory '{$outputDir}' was not created");
            }
        }
        $answers = $user->answers()->get();
        $specificationDocument = new SpecificationDocument($outputDir, $user, $answers);
        $specificationDocument->save(true);

        event(new ExportedDocument($user, $specificationDocument));

        return $specificationDocument->toResponse($request);
    }
}
