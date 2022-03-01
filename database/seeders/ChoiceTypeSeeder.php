<?php

namespace Database\Seeders;

use App\Models\ChoiceType;
use App\Models\Text;
use Illuminate\Database\Seeder;
use RuntimeException;
use Yaml;

class ChoiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $choiceTypes = Yaml::parse(file_get_contents(database_path('/data/00_choices.yaml')));

        foreach ($choiceTypes as $type => $choiceType) {
            /*
             * @var \App\Models\ChoiceType
             */
            $newChoiceType = ChoiceType::updateOrCreate(
                ['type' => $type],
                [
                    'tiles'    => $choiceType['tiles'] ?? false,
                    'multiple' => $choiceType['multiple'] ?? false,
                ]
            );
            $options = $choiceType['options'];
            $sorting = 0;
            foreach ($options as $i18nId => $option) {
                $textId = null;
                $valueId = null;
                if (is_string($option)) {
                    $this->text($i18nId, $option);
                    $textId = $i18nId;
                    $valueId = $i18nId;
                } elseif (is_array($option)) {
                    $textId = $i18nId.'.text';
                    $this->text($textId, $option['text']);
                    $valueId = $i18nId.'.value';
                    $this->text($valueId, $option['value'], false);
                }
                if (! $textId || ! $valueId) {
                    throw new RuntimeException('invalid choice option config');
                }
                $newChoiceType->options()->updateOrCreate(
                    [
                        'type'           => $i18nId,
                    ],
                    [
                        'choice_type_id' => $newChoiceType->id,
                        'text'           => $textId,
                        'value'          => $valueId,
                        'sort'           => $sorting,
                    ]
                );
                if (isset($option['other'])) {
                    $textId = $i18nId.'.other.content';
                    $this->text($textId, $option['other']);
                }
                if (isset($option['otherhint'])) {
                    $textId = $i18nId.'.other.sub_content';
                    $this->text($textId, $option['otherhint']);
                }
                $sorting++;
            }
        }
    }

    protected function text($key, $value, $public = true)
    {
        Text::firstOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'public' => $public,
            ]
        );
    }
}
