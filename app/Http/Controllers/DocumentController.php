<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecificationDocumentExcel;
use App\Mail\DocumentGeneratedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DocumentController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAllRequiredFieldsForSpecificationDocument()) {
            return response('', 428);
        }
        $outputDir = storage_path('app/export');
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }
        $filename              = $outputDir.DIRECTORY_SEPARATOR.uniqid($user->id.'_').'.xlsx';
        $answers               = $user->answers()->get();
        $specificationDocument = new SpecificationDocumentexcel($filename, $user, $answers);
        $specificationDocument->save();
        $mail = new DocumentGeneratedMail($user);
        $mail->attach($filename);
        Mail::to(config('mail.recipient.lead.address'))
            ->sendNow($mail);

        return $specificationDocument->toResponse($request);
    }
}
