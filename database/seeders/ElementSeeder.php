<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\ChoiceType;
use App\Models\Element;
use App\Models\Locale;
use App\Models\Section;
use App\Models\Text;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Yaml;

class ElementSeeder extends Seeder
{
    protected $presets;

    protected $locale;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->locale = Locale::DE;
        $this->preparePresets();
        $this->import('10_firmenprofil.yaml');
        $this->import('20_organisation.yaml');
        $this->import('30_infrastruktur.yaml');
        $this->import('40_systemeinsatz.yaml');
        $this->import('50_industrie_4_0.yaml');
        $this->import('60_anforderungen.yaml');
        $this->import('70_projekt.yaml');
        $this->import('80_erp_anbieter.yaml');
    }

    protected function createKeyName(...$keys)
    {
        return implode('.', $keys);
    }

    protected function preparePresets()
    {
        $this->presets = Yaml::parse(file_get_contents(database_path('/data/01_element_presets.yaml')));
    }

    protected function import($fileName)
    {
        $chapters = Yaml::parse(file_get_contents(database_path('/data/'.$fileName)));

        foreach ($chapters as $i18nId => $chapter) {
            $this->importChapter($i18nId, $chapter);
        }
    }

    protected function importChapter($i18nId, $chapter)
    {
        if (isset($chapter['name'])) {
            $this->text($i18nId, $chapter['name']);
        }
        if (isset($chapter['description'])) {
            $descriptionKey = $this->createKeyName($i18nId, 'description');
            $this->text($descriptionKey, $chapter['description']);
        } else {
            $descriptionKey = null;
        }
        $sort = $chapter['sort'] ?? 0;
        /**
         * @var Chapter
         */
        $newChapter = Chapter::updateOrCreate(
            ['name' => $i18nId],
            [
                'slug_name'           => Str::slug($chapter['name']),
                'sort'                => $sort,
                'description'         => $descriptionKey,
                'visible'             => $chapter['visible'] ?? true,
                'illustration_states' => $chapter['illustration'] ?? null,
                'worksheet'           => (int) $chapter['worksheet'],
            ]
        );
        $sectionSorting = 0;
        $sectionIds = [];
        foreach ($chapter['sections'] as $sectionI18nId => $section) {
            $section['sort'] = $sectionSorting;
            $newSection = $this->importSection($newChapter, $sectionI18nId, $section);
            $sectionSorting++;
            if ($newSection) {
                $sectionIds[] = $newSection->id;
            }
        }
        $newChapter->sections()->whereNotIn('id', $sectionIds)->delete();
    }

    protected function importSection($chapter, $i18nId, $section)
    {
        $sectionHeadlineKey = $this->createKeyName($i18nId, 'headline');
        $sectionDescriptionKey = $this->createKeyName($i18nId, 'description');
        $hasHeadline = false;
        if (isset($section['headline'])) {
            $this->text($sectionHeadlineKey, $section['headline']);
            $hasHeadline = true;
        }
        if (isset($section['description'])) {
            $this->text($sectionDescriptionKey, $section['description']);
        } else {
            $sectionDescriptionKey = '';
        }
        $sectionSorting = $section['sort'] ?? 0;
        /**
         * @var Section
         */
        $newSection = Section::updateOrCreate(
            [
                'headline' => $sectionHeadlineKey,
            ],
            [
                'slug_name'           => Str::slug($section['slug_name'] ?? $section['headline']),
                'description'         => $sectionDescriptionKey,
                'sort'                => $sectionSorting,
                'chapter_id'          => $chapter->id,
                'has_headline'        => $hasHeadline,
                'illustration_states' => $section['illustration'] ?? null,
            ]
        );
        if (! isset($section['elements'])) {
            return null;
        }
        $elementsIds = [];
        $elements = $section['elements'];
        $sorting = 0;
        $documentRowOffset = $section['document_offset_row'] ?? 0;
        foreach ($elements as $id => $element) {
            if (is_string($element)) {
                // element is a preset
                // use element as key of preset
                $element = Arr::get($this->presets, $element);
            }
            $data = [];
            $data['type'] = $element['type'];
            if (isset($element['content'])) {
                $contentKey = $this->createKeyName($i18nId, $id, 'content');
                $this->text($contentKey, $element['content']);
                $data['content'] = $contentKey;
            }
            if (isset($element['sub_content'])) {
                $subContentKey = $this->createKeyName($i18nId, $id, 'sub_content');
                $this->text($subContentKey, $element['sub_content']);
                $data['sub_content'] = $subContentKey;
            }

            $this->optionalValue('steps', $element, $data);
            $this->optionalValue('min', $element, $data);
            $this->optionalValue('max', $element, $data);
            if (isset($element['choice_type'])) {
                $choiceType = ChoiceType::where('type', '=', $element['choice_type'])->get(['id'])->first();
                $data['choice_type_id'] = $choiceType->id;
            }
            $data['sort'] = $sorting;
            $data['section_id'] = $newSection->id;
            $data['layout_two_columns'] = $element['layout_two_columns'] ?? false;
            $data['illustration_states'] = $element['illustration'] ?? null;
            if (isset($element['document_row'])) {
                $data['document_row'] = $documentRowOffset + (int) $element['document_row'];
            }
            $newElement = Element::updateOrCreate(['content' => $contentKey], $data);
            $sorting++;
            $elementsIds[] = $newElement->id;
        }
        $newSection->elements()->whereNotIn('id', $elementsIds)->delete();

        return $newSection;
    }

    protected function text($key, $value)
    {
        Text::firstOrCreate(
            [
                'key' => $key,
                'locale' => $this->locale,
            ], [
                'value' => $value,
            ]
        );
    }

    protected function optionalValue($key, $import, &$output)
    {
        if (isset($import[$key])) {
            $output[$key] = $import[$key];
        }
    }
}
