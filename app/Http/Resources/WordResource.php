<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Responsable;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;

abstract class WordResource implements Responsable
{
    /**
     * @var PhpWord
     */
    protected $document;
    protected $filename;
    private $saved                      = false;
    protected $template                 = null;
    protected $defaultTextStyleName     = 'default';
    protected $tocTitleStyleName        = 'tocTitle';
    protected $additionalTitleStyleName = 'additionalTitle';
    protected $tocStyle;
    protected $defaultFontName  = 'Calibri';
    protected $defaultTextColor = '404040';

    /**
     * WordResource constructor.
     *
     * @param $filename
     *
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function __construct($filename)
    {
        Settings::setTempDir(storage_path('app/tmp'));
        $this->filename = $filename;
        $this->document = $this->createDocument();
        $this->addDefaultStyles();
        $this->renderDocument($this->document);
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
     * @return PhpWord
     *
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    protected function createDocument(): PhpWord
    {
        if ($this->template) {
            $templateProcessor = new TemplateProcessor(resource_path($this->template));
            $this->replaceTemplateMarker($templateProcessor);
            $templateProcessor->saveAs($this->filename);

            return IOFactory::load($this->filename);
        }

        return new PhpWord();
    }

    protected function addDefaultStyles()
    {
        $this->document->setDefaultFontName($this->defaultFontName);
        $this->document->addNumberingStyle(
            'headlineNumbering',
            ['type' => 'multilevel', 'levels' => [
                ['pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'],
                ['pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2'],
                ['pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3'],
            ],
            ]
        );
        $this->document->addFontStyle($this->defaultTextStyleName, [
            'size'  => 12,
            'color' => $this->defaultTextColor,
        ]);

        $this->document->addTitleStyle(1, [
            'size'  => 16,
            'color' => $this->defaultTextColor,
        ], ['numStyle' => 'headlineNumbering', 'numLevel' => 0]);
        $this->document->addTitleStyle(2, [
            'size'  => 14,
            'color' => $this->defaultTextColor,
        ], ['numStyle' => 'headlineNumbering', 'numLevel' => 1]);
        $this->document->addTitleStyle(3, [
            'size'  => 12,
            'color' => $this->defaultTextColor,
        ], ['numStyle' => 'headlineNumbering', 'numLevel' => 2]);

        $this->document->addFontStyle($this->tocTitleStyleName, [
            'size'  => 16,
            'color' => $this->defaultTextColor,
            'bold'  => true,
        ]);

        $this->document->addFontStyle($this->additionalTitleStyleName, [
            'size'  => 14,
            'color' => $this->defaultTextColor,
        ]);
    }

    abstract protected function replaceTemplateMarker(TemplateProcessor $templateProcessor);

    abstract protected function renderDocument(PhpWord $document);

    public function save()
    {
        if (!$this->saved) {
            $this->document->save($this->filename);
            $this->saved = true;
        }
    }

    protected function percentageToPhpWordPercentage($value)
    {
        // percentage is in fiftieths (1/50) of a percent (1% = 50 unit)
        return $value * 50;
    }

    protected function twipToPT($value)
    {
        return (float) $value / (float) Converter::INCH_TO_TWIP * Converter::INCH_TO_POINT;
    }

    protected function twipToPixel($value)
    {
        return (float) $value / (float) Converter::INCH_TO_TWIP * Converter::INCH_TO_PIXEL;
    }
}
