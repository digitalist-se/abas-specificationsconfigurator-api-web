<?php

namespace App\Http\Resources;

use App\Exceptions\GenerateExcelException;
use Illuminate\Contracts\Support\Responsable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class ExcelResource implements Responsable
{
    const EXTENSION_XLSX = '.xlsx';
    const EXTENSION_ZIP  = '.zip';
    /**
     * @var Spreadsheet
     */
    protected $document;
    protected $outputDir;
    protected $filename;
    private $saved      = false;
    protected $template = null;
    private $localDocumentFilename;

    /**
     * WordResource constructor.
     *
     * @param $outputDir
     * @param $filename
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct($outputDir, $filename, $localDocumentFilename)
    {
        $this->outputDir = $outputDir;
        $this->filename  = $filename;
        $this->document  = $this->createDocument();
        $this->renderDocument();
        $this->localDocumentFilename = $localDocumentFilename;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws GenerateExcelException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function toResponse($request)
    {
        $this->save();

        $response = response()->download($this->outputZipFilename(), $this->filename.self::EXTENSION_ZIP);
        $response->headers->remove('cache-control');
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('post-check=0', true);
        $response->headers->addCacheControlDirective('pre-check=0', true);

        return $response;
    }

    /**
     * @return Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function createDocument(): Spreadsheet
    {
        if ($this->template) {
            return \PhpOffice\PhpSpreadsheet\IOFactory::load(resource_path($this->template));
        }

        return new Spreadsheet();
    }

    /**
     * @param string $key
     * @param $value
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function replaceTemplateMarker(String $key, $value)
    {
        $markerKey = '{'.$key.'}';
        foreach ($this->document->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                try {
                    $cellIterator->setIterateOnlyExistingCells(true);
                } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
                }
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() === $markerKey) {
                        $cell->setValue($value);
                    }
                }
            }
        }
    }

    /**
     * @param string $key
     * @param $value
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function replaceHeaderMarker(String $key, $value)
    {
        foreach ($this->document->getWorksheetIterator() as $worksheet) {
            $headerFooter = $worksheet->getHeaderFooter();
            $headerFooter->setOddHeader(str_replace($key, $value, $headerFooter->getOddHeader()));
        }
    }

    abstract protected function renderDocument();

    public function outputExcelFilename()
    {
        return $this->outputDir.DIRECTORY_SEPARATOR.$this->filename.self::EXTENSION_XLSX;
    }

    public function outputZipFilename()
    {
        return $this->outputDir.DIRECTORY_SEPARATOR.$this->filename.self::EXTENSION_ZIP;
    }

    protected function saveDocument()
    {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->document, 'Xlsx');
        $writer->save($this->outputExcelFilename());
        if (!file_exists($this->outputExcelFilename())) {
            throw \Exception('document was not generated');
        }
    }

    protected function zipDocument()
    {
        $zip        = new \ZipArchive();
        $openResult = $zip->open($this->outputZipFilename(), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if (true !== $openResult) {
            throw new \Exception('could not open zip archive');
        }
        $addFileResult = $zip->addFile($this->outputExcelFilename(), $this->localDocumentFilename);
        if (true !== $addFileResult) {
            throw new \Exception('could not add document ('.$this->outputExcelFilename().' to zip archive');
        }
        $zip->close();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws GenerateExcelException
     */
    public function save()
    {
        if (!$this->saved) {
            try {
                $this->saveDocument();
                $this->zipDocument();
                unlink($this->outputExcelFilename());
                $this->saved = true;
            } catch (\Exception $exception) {
                throw new GenerateExcelException($exception);
            }
        }
    }
}
