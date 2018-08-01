<?php

use App\Models\Element;
use Illuminate\Database\Seeder;

class ElementSeeder extends Seeder
{
    protected $presets;

    /**
     * Run the database seeds.
     */
    public function run()
    {
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
        $this->presets       = Yaml::parse(file_get_contents(database_path('/data/01_element_presets.yaml')));
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
        $printNameKey = '';
        if (isset($chapter['print_name'])) {
            $printNameKey = $this->createKeyName($i18nId, 'print');
            $this->text($printNameKey, $chapter['print_name']);
        }
        if (isset($chapter['description'])) {
            $descriptionKey = $this->createKeyName($i18nId, 'description');
            $this->text($descriptionKey, $chapter['description']);
        } else {
            $descriptionKey = null;
        }
        if (isset($chapter['print_description'])) {
            $printDescriptionKey = $this->createKeyName($i18nId, 'print_description');
            $this->text($printDescriptionKey, $chapter['print_description']);
        } else {
            $printDescriptionKey = null;
        }
        $sort       = $chapter['sort'] ?? 0;
        $newChapter = \App\Models\Chapter::updateOrCreate(
            ['name' => $i18nId],
            [
                'print_name'          => $printNameKey,
                'slug_name'           => str_slug($chapter['name']),
                'sort'                => $sort,
                'description'         => $descriptionKey,
                'print_description'   => $printDescriptionKey,
                'visible'             => $chapter['visible'] ?? true,
                'illustration_states' => $chapter['illustration'] ?? null,
            ]
        );
        $sectionSorting = 0;
        foreach ($chapter['sections'] as $i18nId => $section) {
            $section['sort'] = $sectionSorting;
            $this->importSection($newChapter, $i18nId, $section);
            ++$sectionSorting;
        }
    }

    protected function importSection($chapter, $i18nId, $section)
    {
        $sectionHeadlineKey         = $this->createKeyName($i18nId, 'headline');
        $sectionDescriptionKey      = $this->createKeyName($i18nId, 'description');
        $sectionPrintDescriptionKey = $this->createKeyName($i18nId, 'print_description');
        $hasHeadline                = false;
        if (isset($section['headline'])) {
            $this->text($sectionHeadlineKey, $section['headline']);
            $hasHeadline = true;
        }
        if (isset($section['description'])) {
            $this->text($sectionDescriptionKey, $section['description']);
        } else {
            $sectionDescriptionKey = '';
        }
        if (isset($section['print_description'])) {
            $this->text($sectionPrintDescriptionKey, $section['print_description']);
        } else {
            $sectionPrintDescriptionKey = '';
        }
        $sectionSorting = $section['sort'] ?? 0;
        $newSection     = \App\Models\Section::updateOrCreate(
            [
                'headline' => $sectionHeadlineKey,
            ],
            [
                'slug_name'           => str_slug($section['slug_name'] ?? $section['headline']),
                'description'         => $sectionDescriptionKey,
                'print_description'   => $sectionPrintDescriptionKey,
                'sort'                => $sectionSorting,
                'chapter_id'          => $chapter->id,
                'has_headline'        => $hasHeadline,
                'illustration_states' => $section['illustration'] ?? null,
            ]
        );
        if (!isset($section['elements'])) {
            return;
        }
        $elements       = $section['elements'];
        $sorting        = 0;
        foreach ($elements as $id => $element) {
            if (is_string($element)) {
                // element is an preset
                // use element as key of preset
                $element = array_get($this->presets, $element);
            }
            $data         = [];
            $data['type'] = $element['type'];
            if (isset($element['content'])) {
                $contentKey = $this->createKeyName($i18nId, $id, 'content');
                $this->text($contentKey, $element['content']);
                $data['content'] = $contentKey;
            } elseif ('print_headline' !== $element['type']) {
                return;
            }
            if (isset($element['sub_content'])) {
                $subContentKey = $this->createKeyName($i18nId, $id, 'sub_content');
                $this->text($subContentKey, $element['sub_content']);
                $data['sub_content'] = $contentKey;
            }
            if (isset($element['print'])) {
                $printKey = $this->createKeyName($i18nId, $id, 'print');
                $this->text($printKey, $element['print']);
                $data['print'] = $printKey;
            } else {
                $data['print'] = null;
            }
            $this->optionalValue('steps', $element, $data);
            $this->optionalValue('min', $element, $data);
            $this->optionalValue('max', $element, $data);
            if (isset($element['choice_type'])) {
                $choiceType             = \App\Models\ChoiceType::where('type', '=', $element['choice_type'])->get(['id'])->first();
                $data['choice_type_id'] = $choiceType->id;
            }
            $data['sort']                = $sorting;
            $data['section_id']          = $newSection->id;
            $data['layout_two_columns']  = $element['layout_two_columns'] ?? false;
            $data['illustration_states'] = $element['illustration'] ?? null;
            Element::updateOrCreate(['content' => $contentKey], $data);
            ++$sorting;
        }
    }

    protected function text($key, $value)
    {
        \App\Models\Text::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    protected function optionalValue($key, $import, &$output)
    {
        if (isset($import[$key])) {
            $output[$key] = $import[$key];
        }
    }

    protected function validateElement($element)
    {
//        $element['type']
//        switch($element['type']) {
//
//        }
    }
}
