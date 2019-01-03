<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Responsable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class ExcelResource implements Responsable
{
    /**
     * @var Spreadsheet
     */
    protected $document;
    protected $filename;
    private $saved      = false;
    protected $template = null;

    /**
     * WordResource constructor.
     *
     * @param $filename
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->document = $this->createDocument();
        $this->renderDocument();
    }

    public function toResponse($request)
    {
        $this->save();

        $response = response()->download($this->filename);
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

    abstract protected function renderDocument();

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save()
    {
        if (!$this->saved) {
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->document, 'Xlsx');
            $writer->save($this->filename);
            $this->saved = true;
        }
    }
}
