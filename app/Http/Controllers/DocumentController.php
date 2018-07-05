<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecificationDocument;
use App\Mail\DocumentGeneratedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DocumentController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function generate(Request $request)
    {
        if (!$request->user()->hasAllRequiredFieldsForSpecificationDocument()) {
            return response('', 428);
        }
        $outputDir = storage_path('app/export');
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }
        $filename              = $outputDir.DIRECTORY_SEPARATOR.uniqid($request->user()->id.'_').'.docx';
        $answers               = $request->user()->answers()->get();
        $specificationDocument = new SpecificationDocument($filename, $answers);
        $specificationDocument->save();
        $mail = new DocumentGeneratedMail($request->user());
        $mail->attach($filename);
        Mail::to(config('mail.recipient.lead.address'))->send($mail);

        return $specificationDocument->toResponse($request);
    }
}
