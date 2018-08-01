<?php

use Illuminate\Database\Seeder;

class ChoiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $choiceTypes = Yaml::parse(file_get_contents(database_path('/data/00_choices.yaml')));

        foreach ($choiceTypes as $type => $choiceType) {
            /**
             * @var \App\Models\ChoiceType
             */
            $newChoiceType = \App\Models\ChoiceType::updateOrCreate(
                ['type' => $type],
                [
                    'tiles'    => $choiceType['tiles'] ?? false,
                    'multiple' => $choiceType['mulitple'] ?? false,
                ]
            );
            $options = $choiceType['options'];
            $sorting = 0;
            foreach ($options as $i18nId => $option) {
                $textId  = null;
                $valueId = null;
                if (is_string($option)) {
                    \App\Models\Text::updateOrCreate([
                        'key'   => $i18nId,
                        ], [
                        'value' => $option,
                    ]);
                    $textId  = $i18nId;
                    $valueId = $i18nId;
                } elseif (is_array($option)) {
                    $textId = $i18nId.'.text';
                    \App\Models\Text::updateOrCreate([
                        'key'   => $textId,
                    ], [
                        'value' => $option['text'],
                    ]);
                    $valueId = $i18nId.'.value';
                    \App\Models\Text::updateOrCreate([
                        'key'   => $valueId,
                    ], [
                        'value'  => $option['value'],
                        'public' => false,
                    ]);
                }
                if (!$textId || !$valueId) {
                    throw new \RuntimeException('invalid choice option config');
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
                    $textId = $i18nId.'.other';
                    \App\Models\Text::updateOrCreate([
                        'key'   => $textId,
                    ], [
                        'value' => $option['other'],
                    ]);
                }
                ++$sorting;
            }
        }
    }
}
