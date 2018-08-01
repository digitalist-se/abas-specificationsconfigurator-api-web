<?php

namespace App\Http\Resources;

use App\Models\Text;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Image as ImageStyle;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\Style\TOC;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Style\Line as LineStyle;

class SpecificationDocument extends WordResource
{
    protected $template              = 'word/specification_configurator_template.docx';
    protected $templateTextVariables = [
        'document.title',
        'document.subtitle',
        'document.copyright',
        'document.logoPlaceholder',
        'document.tocTitle',
    ];
    protected $footerImage = 'word/generator_logo.jpg';
    protected $introImage  = 'word/intro.png';
    /**
     * @var \PhpOffice\PhpWord\Element\Section
     */
    protected $mainSection;
    private $answersMap;

    /**
     * SpecificationDocument constructor.
     *
     * @param $filename
     * @param \App\Models\Answer[] $answers
     *
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function __construct($filename, $answers)
    {
        $this->answersMap = [];
        foreach ($answers as $answer) {
            $this->answersMap[$answer->element_id] = $answer;
        }
        parent::__construct($filename);
    }

    protected function localizedText($key)
    {
        $text = Text::where('key', '=', $key)
            ->where('locale', '=', 'de')
            ->first();
        if ($text instanceof Text) {
            return htmlspecialchars($text->value);
        }

        return $key;
    }

    protected function replaceTemplateMarker(TemplateProcessor $templateProcessor)
    {
        foreach ($this->templateTextVariables as $textVariable) {
            $templateProcessor->setValue($textVariable, $this->localizedText($textVariable));
        }
    }

    protected function renderDocument(PhpWord $document)
    {
        $this->defineMainSection($document);
        if (!$this->mainSection) {
            return;
        }
        $this->addHeaderToSection();
        $this->addFooterToSection();
        $this->addIntroImage();
        $this->addTableOfContent();
        $this->mainSection->addPageBreak();
        $this->addMainContent();
    }

    protected function defineMainSection(PhpWord $document)
    {
        $section  = null;
        $sections = $document->getSections();
        if (count($sections)) {
            $section = $sections[0];
        } else {
            $section = $document->addSection();
        }
        if (!$section) {
            return;
        }
        $this->mainSection = $section;
    }

    protected function addHeaderToSection()
    {
        $header = $this->mainSection->addHeader();
        $header->addText($this->localizedText('document.logoPlaceholder'), null, [
            'alignment' => Jc::END,
        ]);
        $lineStyle = new LineStyle();
        $lineStyle->setUnit(LineStyle::UNIT_PX);
        $lineStyle->setHeight(0);
        $width = $this->mainSection->getStyle()->getPageSizeW() + $this->mainSection->getStyle()->getMarginLeft() + $this->mainSection->getStyle()->getMarginRight();
        $width = $this->twipToPixel($width);
        $lineStyle->setWidth($width);
        $lineStyle->setColor('A5A5A5');
        $lineStyle->setPos(LineStyle::POSITION_ABSOLUTE);
        $lineStyle->setPosHorizontal(LineStyle::POSITION_ABSOLUTE);
        $lineStyle->setPosHorizontalRel(LineStyle::POSITION_RELATIVE_TO_PAGE);
        $lineStyle->setPosVertical(LineStyle::POSITION_ABSOLUTE);
        $lineStyle->setPosVerticalRel(LineStyle::POSITION_RELATIVE_TO_TEXT);
        $lineStyle->setTop(\PhpOffice\PhpWord\Shared\Converter::cmToPixel(0.91));
        $lineStyle->setWrappingStyle(LineStyle::WRAPPING_STYLE_BEHIND);
        $header->addLine($lineStyle);
    }

    protected function addFooterToSection()
    {
        $footer = $this->mainSection->addFooter();
        $width  = $this->percentageToPhpWordPercentage(100);
        $row    = $footer->addTable(['width' => $width, 'unit' => Table::WIDTH_PERCENT])->addRow();
        $row->addCell()->addImage(resource_path($this->footerImage), [
            'width'         => 113,
            'unit'          => ImageStyle::UNIT_PX,
            'wrappingStyle' => ImageStyle::WRAPPING_STYLE_BEHIND,
        ]);
        $row->addCell()->addText($this->localizedText('document.copyright'), null, [
            'alignment' => Jc::END,
        ]);
    }

    protected function addIntroImage()
    {
        $sectionStyle = $this->mainSection->getStyle();
        $width        = $sectionStyle->getPageSizeW();
        $width        = $this->twipToPT($width);
        $this->mainSection->addImage(resource_path($this->introImage), [
            'width'         => $width,
            'unit'          => ImageStyle::UNIT_PX,
        ]);
    }

    protected function addTableOfContent()
    {
        $this->document->getSettings()->setUpdateFields(true); // required to update page numbers in toc
        $this->mainSection->addText($this->localizedText('document.tocTitle'), $this->tocTitleStyleName);
        $this->mainSection->addTOC(['color' => $this->defaultTextColor], [
            'tabLeader' => TOC::TAB_LEADER_NONE,
        ]);
    }

    protected function addMainContent()
    {
        $chapters        = \App\Models\Chapter::orderBy('sort')->get();
        $documentSection = $this->mainSection;
        foreach ($chapters as $chapter) {
            /*
             * @var \App\Models\Chapter $chapter
             */
            $documentSection->addTitle($this->localizedText($chapter->print_name), 1);
            if ($chapter->print_description) {
                $this->addLocalizedText($chapter->print_description);
            }
            $contentSections = $chapter->sections;
            foreach ($contentSections as $contentSection) {
                if ($contentSection->has_headline && !empty($contentSection->headline)) {
                    $documentSection->addTitle($this->localizedText($contentSection->headline), 2);
                }
                if (!empty($contentSection->print_description)) {
                    $this->addLocalizedText($contentSection->print_description);
                }
                $contentElements = $contentSection->printableElements;
                foreach ($contentElements as $contentElement) {
                    $this->addContentElement($contentElement);
                }
            }
            $documentSection->addPageBreak();
        }
    }

    protected function addLocalizedText($textKey)
    {
        if (!$textKey) {
            return;
        }
        $this->mainSection->addText($this->localizedText($textKey, $this->defaultTextStyleName));
    }

    protected function addContentElement(\App\Models\Element $contentElement)
    {
        if ('print_headline' === $contentElement->type) {
            if (!empty($contentElement->print)) {
                $this->mainSection->addText($this->localizedText($contentElement->print), $this->additionalTitleStyleName);
            }

            return;
        }
        if (isset($this->answersMap[$contentElement->id])) {
            $answer = $this->answersMap[$contentElement->id];
            if ($answer && isset($answer->value)) {
                $parsedAnswerValue = '';
                switch ($contentElement->type) {
                    case 'text':
                        $parsedAnswerValue = htmlspecialchars($answer->value->text);
                        break;
                    case 'choice':
                        if ($contentElement->choiceType->multiple) {
                            if (!is_array($answer->value->options)) {
                                continue;
                            }
                            $options       = $answer->value->options;
                            $parsedOptions = [];
                            foreach ($options as $option) {
                                if ('branche.option.other.value' === $option) {
                                    // other is enabled.
                                    continue;
                                }
                                $parsedOptions[] = $this->localizedText($option);
                            }
                            $parsedAnswerValue = join(', ', $parsedOptions);
                        } else {
                            if (!isset($answer->value->option)) {
                                continue;
                            }
                            $parsedAnswerValue = $this->localizedText($answer->value->option);
                        }
                        if (isset($answer->value->otherEnabled)
                            && $answer->value->otherEnabled
                            && isset($answer->value->otherValue)
                            && !empty($answer->value->otherValue)
                        ) {
                            if (empty($parsedAnswerValue)) {
                                $parsedAnswerValue = $answer->value->otherValue;
                            } else {
                                $parsedAnswerValue .= ', '.htmlspecialchars($answer->value->otherValue);
                            }
                        }
                        break;
                }
                if (!empty($parsedAnswerValue)) {
                    $this->addLocalizedText($contentElement->print);
                    $this->mainSection->addText($parsedAnswerValue, $this->defaultTextStyleName);
                }
            }
        }
    }
}
