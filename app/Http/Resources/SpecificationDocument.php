<?php

namespace App\Http\Resources;

use App\Models\Locale;
use App\Models\Text;
use Illuminate\Support\Facades\Log;

class SpecificationDocument extends ExcelResource
{
    private function localizedTemplate() : String {
        $locale = Locale::current();

        return "excel/{$locale->getValue()}/specification_configurator_template.xlsx";
    }

    protected $template = null;

    protected $userInfoMap = [
        'B2'  => 'company_name',
        'B3'  => 'street',
        'B4'  => 'zipcode_and_city',
        'B5'  => 'country', // not available
        'B6'  => 'phone',
        'B7'  => 'email',
        'B8'  => 'website',
        'B9'  => 'contact',
        'B10' => 'contact_function',
    ];

    private $answersMap;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * SpecificationDocument constructor.
     *
     * @param $outputDir
     * @param $user
     * @param \App\Models\Answer[] $answers
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct($outputDir, $user, $answers)
    {
        $this->template   = $this->localizedTemplate();
        $this->user       = $user;
        $this->answersMap = [];
        foreach ($answers as $answer) {
            $this->answersMap[$answer->element_id] = $answer;
        }
        $filename              = uniqid($user->id.'_', true);
        $localDocumentFilename = trans('specification.filename').self::EXTENSION_XLSX;
        parent::__construct($outputDir, $filename, $localDocumentFilename);
    }

    protected function localizedText($key)
    {
        $text = Text::where('key', '=', $key)
            ->where('locale', '=', Locale::current()->getValue())
            ->first();
        if ($text instanceof Text) {
            return htmlspecialchars($text->value);
        }

        return $key;
    }


    protected function renderDocument()
    {
        $this->renderUserInfoValues();
        $this->renderContentValues();
        $this->document->setActiveSheetIndex($this->document->getFirstSheetIndex());
        $headerFooter = $this->document->getActiveSheet()->getHeaderFooter();
        $this->replaceHeaderMarker(trans('specification.companyHeader'), $this->user->company_name, $headerFooter->getOddHeader());
    }

    private function renderUserInfoValues()
    {
        $firstSheet = $this->document->getSheet(0);
        foreach ($this->userInfoMap as $cellId => $keyValue) {
            if ($cell = $firstSheet->getCell($cellId)) {
                $cell->setValue(object_get($this->user, $keyValue, ''));
            }
        }
    }

    protected function renderContentValues()
    {
        $chapters        = \App\Models\Chapter::orderBy('sort')->get();
        foreach ($chapters as $chapter) {
            /**
             * @var \App\Models\Chapter
             */
            $worksheet       = $chapter->worksheet;
            $contentSections = $chapter->sections;
            foreach ($contentSections as $contentSection) {
                $contentElements = $contentSection->printableElements;
                foreach ($contentElements as $contentElement) {
                    $this->addContentElement($worksheet, $contentElement);
                }
            }
        }
    }

    protected function addLocalizedText(int $worksheet, string $cellId, $textKey)
    {
        if (!$cellId || !$textKey) {
            return;
        }
        $value = $this->localizedText($textKey);
        $this->addText($worksheet, $cellId, $value);
    }

    protected function addText(int $worksheet, string $cellId, $text)
    {
        if (!$cellId || !$text) {
            return;
        }
        if($cell = $this->document->getSheet($worksheet)->getCell($cellId)) {
            $cell->setValue($text);
        }
    }

    protected function addContentElement(int $worksheet, \App\Models\Element $contentElement)
    {
        if (!isset($this->answersMap[$contentElement->id])) {
            return;
        }
        $answer = $this->answersMap[$contentElement->id];
        if ($answer && isset($answer->value)) {
            $parsedAnswerValue = '';
            switch ($contentElement->type) {
                case 'text':
                    if (isset($answer->value, $answer->value->text)) {
                        $parsedAnswerValue = htmlspecialchars($answer->value->text);
                    }
                    break;
                case 'slider':
                    if (isset($answer->value, $answer->value->value)) {
                        $parsedAnswerValue = htmlspecialchars($answer->value->value);
                    }
                    break;
                case 'choice':
                    if ('lights' === $contentElement->choiceType->type) {
                        if (!isset($answer->value->option)) {
                            return;
                        }
                        $column = $this->getColumnOfLightChoiceOption($answer->value->option);
                        if (!$column) {
                            return;
                        }
                        $this->addText($worksheet, $column.$contentElement->document_row, 'x');

                        return;
                    }
                    if ($contentElement->choiceType->multiple) {
                        if (!is_array($answer->value->options)) {
                            break;
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
                        $parsedAnswerValue = implode(', ', $parsedOptions);
                    } else {
                        if (!isset($answer->value->option)) {
                            break;
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
                $cellId = $contentElement->document_cell;
                if (!$cellId) {
                    Log::error('missing cellId for content element: '.$contentElement->id);

                    return;
                }
                $this->addText($worksheet, $cellId, $parsedAnswerValue);
            }
        }
    }

    protected function getColumnOfLightChoiceOption($value)
    {
        switch ($value) {
            case 'lights.option.green':
                return 'C';
            case 'lights.option.yellow':
                return 'D';
            case 'lights.option.red':
                return 'E';
        }

        return null;
    }
}
