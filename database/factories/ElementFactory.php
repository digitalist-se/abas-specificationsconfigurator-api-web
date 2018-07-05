<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Chapter::class, function (Faker $faker) {
});

$factory->define(App\Models\Section::class, function (Faker $faker) {
});

$factory->define(App\Models\Element::class, function (Faker $faker) {
    $text = factory(\App\Models\Text::class)->create();
    $chapter = \App\Models\Chapter::create([
        'name'            => $text->key,
        'print_name'      => $text->key,
        'slug_name'       => str_slug($text->value),
        'sort'            => 0,
    ]);
    $section = \App\Models\Section::create([
        'headline'    => $text->key,
        'description' => $text->key,
        'slug_name'   => str_slug($text->value),
        'sort'        => 0,
        'chapter_id'  => $chapter->id,
    ]);

    return [
        'section_id' => $section->id,
        'type'       => 'text',

        'content' => $text->key,
        'sort'    => 0,

//        // choice type values:
//        'choice_type_id',
//
//        // slider values:
//        'steps',
//        'min',
//        'max',
    ];
});

$factory->define(App\Models\Answer::class, function (Faker $faker) {
    $element = factory(App\Models\Element::class)->create();

    return [
        'element_id' => $element,
        'value'      => $faker->text,
    ];
});
