<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecificationDocument;
use App\Mail\DocumentGeneratedMail;
use Illuminate\Http\Request;
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
        if (!$user->hasAllRequiredFieldsForSpecificationDocument()) {
            return response('', 428);
        }
        $outputDir = storage_path(self::EXPORT_PATH);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir) && !is_dir($outputDir)) {
                throw new RuntimeException("Directory '{$outputDir}' was not created");
            }
        }
        $answers               = $user->answers()->get();
        $specificationDocument = new SpecificationDocument($outputDir, $user, $answers);
        $specificationDocument->save();
        $mail = new DocumentGeneratedMail($user);
        $mail->attach($specificationDocument->outputZipFilename());
        Mail::to(config('mail.recipient.lead.address'))
            ->send($mail);

        return $specificationDocument->toResponse($request);
    }
}
